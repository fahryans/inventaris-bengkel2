<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Bahan;
use App\Models\Kategori;
use App\Models\Laboratorium;
use App\Models\PemakaianBahan;
use App\Models\PemeliharaanAlat;
use App\Models\PeminjamanAlat;
use App\Models\PengadaanAlat;
use App\Models\PengadaanBahan;
use App\Models\UnitAlat;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class ExportController extends Controller
{
    public function export($tipe, Request $request)
    {
        $format = $request->query('format', 'pdf');

        $data = $this->getData($tipe);
        $title = $this->getTitle($tipe);
        $headers = $this->getHeaders($tipe);
        $rows = $this->getRows($tipe, $data);

        if ($format === 'excel') {
            return $this->exportExcel($title, $headers, $rows, $tipe);
        }

        return $this->exportPdf($tipe, $data, $title);
    }

    private function getData($tipe)
    {
        $user = Auth::user();
        $isTeknisi = $user->role === 'teknisi';
        $isKepalaLab = $user->role === 'kepala_labor';

        $labIds = [];
        if ($isTeknisi) {
            $labIds = $user->laboratoriumTeknisi->pluck('id')->toArray();
        } elseif ($isKepalaLab) {
            $labIds = $user->laboratoriumDikelola->pluck('id')->toArray();
        }

        return match($tipe) {
            'alat' => Alat::when($labIds, fn($q) => $q->whereIn('id_labor', $labIds))
                ->with(['kategori', 'laboratorium', 'spesifikasiAlat'])
                ->withCount(['unitAlat' => fn($q) => $q->where('status', 'tersedia')])
                ->withSum('pengadaanAlat', 'jumlah')
                ->withSum(['peminjamanAlat' => fn($q) => $q->active()], 'jumlah')
                ->latest()->get(),
            'bahan' => Bahan::when($labIds, fn($q) => $q->whereIn('id_labor', $labIds))
                ->with('kategori')
                ->withSum('pengadaanBahan', 'stok_tersisa_batch')
                ->latest()->get(),
            'kategori' => Kategori::latest()->get(),
            'laboratorium' => Laboratorium::when($labIds, fn($q) => $q->whereIn('id', $labIds))
                ->with('kalab')->latest()->get(),
            'users' => User::latest()->get(),
            'unit_alat' => UnitAlat::when($labIds, fn($q) => $q->whereHas('alat', fn($q2) => $q2->whereIn('id_labor', $labIds)))
                ->with(['alat', 'spesifikasiAlat'])->latest()->get(),
            'pengadaan_alat' => PengadaanAlat::when($labIds, fn($q) => $q->whereHas('alat', fn($q2) => $q2->whereIn('id_labor', $labIds)))
                ->with(['alat', 'userInput'])->latest()->get(),
            'pengadaan_bahan' => PengadaanBahan::when($labIds, fn($q) => $q->whereHas('bahan', fn($q2) => $q2->whereIn('id_labor', $labIds)))
                ->with(['bahan', 'userInput'])->latest()->get(),
            'peminjaman' => in_array($user->role, ['dosen', 'mahasiswa'])
                ? PeminjamanAlat::where('id_user_peminjam', Auth::id())->with(['alat', 'unitAlat', 'userPeminjam'])->latest()->get()
                : PeminjamanAlat::when($labIds, fn($q) => $q->whereHas('alat', fn($q2) => $q2->whereIn('id_labor', $labIds)))
                    ->with(['alat', 'unitAlat', 'userPeminjam'])->latest()->get(),
            'peminjaman_dikembalikan' => PeminjamanAlat::where('id_user_peminjam', Auth::id())
                ->where('status', 'sudah_dikembalikan')
                ->with(['alat', 'unitAlat', 'userPeminjam'])->latest()->get(),
            'pemakaian_saya' => PemakaianBahan::where('id_user_pemakai', Auth::id())
                ->with(['bahan', 'userPemakai', 'userVerifikasi'])->latest()->get(),
            'pemakaian_bahan' => in_array($user->role, ['dosen', 'mahasiswa'])
                ? PemakaianBahan::where('id_user_pemakai', Auth::id())->with(['bahan', 'userPemakai', 'userVerifikasi'])->latest()->get()
                : PemakaianBahan::when($labIds, fn($q) => $q->whereHas('bahan', fn($q2) => $q2->whereIn('id_labor', $labIds)))
                    ->with(['bahan', 'userPemakai', 'userVerifikasi'])->latest()->get(),
            'pemeliharaan' => PemeliharaanAlat::when($labIds, fn($q) => $q->whereHas('unitAlat.alat', fn($q2) => $q2->whereIn('id_labor', $labIds)))
                ->with(['unitAlat.alat', 'teknisi'])->latest()->get(),
            default => null,
        };
    }

    private function getTitle($tipe)
    {
        return match($tipe) {
            'alat' => 'Data Alat',
            'bahan' => 'Data Bahan',
            'kategori' => 'Data Kategori',
            'laboratorium' => 'Data Laboratorium',
            'users' => 'Data Users',
            'unit_alat' => 'Data Unit Alat',
            'pengadaan_alat' => 'Data Pengadaan Alat',
            'pengadaan_bahan' => 'Data Pengadaan Bahan',
            'peminjaman' => 'Data Peminjaman Alat',
            'peminjaman_dikembalikan' => 'Catatan Pengembalian Alat',
            'pemakaian_bahan' => 'Data Pemakaian Bahan',
            'pemakaian_saya' => 'Catatan Pemakaian Bahan',
            'pemeliharaan' => 'Data Pemeliharaan Alat',
            default => 'Data',
        };
    }

    private function getHeaders($tipe)
    {
        return match($tipe) {
            'alat' => ['No', 'Nama Alat', 'Kategori', 'Laboratorium', 'Tipe Pelacakan', 'Stok Tersedia'],
            'bahan' => ['No', 'Nama Bahan', 'Kategori', 'Satuan', 'Stok Minimum', 'Stok Tersedia'],
            'kategori' => ['No', 'Nama Kategori', 'Jenis'],
            'laboratorium' => ['No', 'Nama Laboratorium', 'Lokasi', 'Kepala Lab'],
            'users' => ['No', 'Nama', 'Email', 'Role', 'No. HP', 'No. Induk', 'Status'],
            'unit_alat' => ['No', 'Kode Inventaris', 'Alat', 'Spesifikasi', 'Kondisi', 'Status'],
            'pengadaan_alat' => ['No', 'Alat', 'Tanggal', 'Jumlah', 'Harga', 'Supplier', 'Status'],
            'pengadaan_bahan' => ['No', 'Bahan', 'Tanggal', 'Jumlah', 'Harga', 'Supplier', 'Status'],
            'peminjaman' => ['No', 'Alat/Unit', 'Peminjam', 'Keperluan', 'Tgl Pinjam', 'Tgl Kembali', 'Status'],
            'peminjaman_dikembalikan' => ['No', 'Alat/Unit', 'Peminjam', 'Keperluan', 'Tgl Pinjam', 'Tgl Kembali Aktual', 'Kondisi Kembali'],
            'pemakaian_bahan' => ['No', 'Bahan', 'Pemakai', 'Keperluan', 'Jumlah Ambil', 'Jumlah Pakai', 'Status'],
            'pemakaian_saya' => ['No', 'Bahan', 'Pemakai', 'Keperluan', 'Jumlah Ambil', 'Jumlah Pakai', 'Status'],
            'pemeliharaan' => ['No', 'Unit Alat', 'Teknisi', 'Tgl Cek', 'Tgl Berikutnya', 'Kondisi', 'Hasil'],
            default => ['No', 'Data'],
        };
    }

    private function getRows($tipe, $data)
    {
        return $data->map(function ($item, $index) use ($tipe) {
            $no = $index + 1;
            return match($tipe) {
                'alat' => [$no, $item->nama_alat, $item->kategori->nama_kategori ?? '-', $item->laboratorium->nama_labor ?? '-', ucfirst($item->tipe_pelacakan), $item->tipe_pelacakan === 'unit' ? $item->unit_alat_count : max(0, ($item->pengadaan_alat_sum_jumlah ?? 0) - ($item->peminjaman_alat_sum_jumlah ?? 0))],
                'bahan' => [$no, $item->nama_bahan, $item->kategori->nama_kategori ?? '-', $item->satuan, $item->stok_minimum, $item->pengadaan_bahan_sum_stok_tersisa_batch ?? 0],
                'kategori' => [$no, $item->nama_kategori, ucfirst($item->jenis)],
                'laboratorium' => [$no, $item->nama_labor, $item->lokasi ?? '-', $item->kalab->nama ?? '-'],
                'users' => [$no, $item->nama, $item->email, ucfirst($item->role), $item->no_hp ?? '-', $item->no_induk ?? '-', ucfirst($item->status)],
                'unit_alat' => [$no, $item->kode_inventaris ?? '-', $item->alat->nama_alat ?? '-', $item->spesifikasiAlat->nama_spesifikasi ?? '-', ucfirst($item->kondisi_saat_ini), ucfirst($item->status)],
                'pengadaan_alat' => [$no, $item->alat->nama_alat ?? '-', $item->tanggal_pengadaan?->format('d/m/Y') ?? '-', $item->jumlah, 'Rp ' . number_format($item->harga_perolehan, 0, ',', '.'), $item->supplier, $item->tanggal_masuk ? 'Diterima' : 'Pending'],
                'pengadaan_bahan' => [$no, $item->bahan->nama_bahan ?? '-', $item->tanggal_pengadaan?->format('d/m/Y') ?? '-', $item->jumlah, 'Rp ' . number_format($item->harga_perolehan, 0, ',', '.'), $item->supplier, $item->tanggal_masuk ? 'Diterima' : 'Pending'],
                'peminjaman' => [$no, $item->equipment_name, $item->userPeminjam->nama ?? '-', $item->keperluan, $item->waktu_peminjaman?->format('d/m/Y H:i') ?? '-', $item->waktu_pengembalian?->format('d/m/Y H:i') ?? '-', ucfirst(str_replace('_', ' ', $item->status))],
                'peminjaman_dikembalikan' => [$no, $item->equipment_name, $item->userPeminjam->nama ?? '-', $item->keperluan, $item->waktu_peminjaman?->format('d/m/Y H:i') ?? '-', $item->waktu_kembali_aktual?->format('d/m/Y H:i') ?? '-', ucfirst(str_replace('_', ' ', $item->kondisi_saat_pengembalian ?? '-'))],
                'pemakaian_bahan' => [$no, $item->bahan->nama_bahan ?? '-', $item->userPemakai->nama ?? '-', $item->keperluan, $item->jumlah_pengambilan, $item->jumlah_terpakai, $item->id_user_verifikasi ? 'Terverifikasi' : 'Menunggu'],
                'pemakaian_saya' => [$no, $item->bahan->nama_bahan ?? '-', $item->userPemakai->nama ?? '-', $item->keperluan, $item->jumlah_pengambilan, $item->jumlah_terpakai, $item->id_user_verifikasi ? 'Terverifikasi' : 'Menunggu'],
                'pemeliharaan' => [$no, $item->unitAlat->alat->nama_alat ?? '-', $item->teknisi->nama ?? '-', $item->tanggal_cek?->format('d/m/Y') ?? '-', $item->tanggal_cek_berikutnya?->format('d/m/Y') ?? '-', $item->kondisi, $item->hasil_pemeliharaan ?? '-'],
                default => [$no, '-'],
            };
        })->toArray();
    }

    private function exportPdf($tipe, $data, $title)
    {
        $template = "laporan.pdf.{$tipe}";

        if (!view()->exists($template)) {
            $template = 'laporan.pdf.template';
        }

        $pdf = Pdf::loadView($template, [
            'data' => $data,
            'title' => $title,
            'date' => now()->format('d/m/Y'),
            'headers' => $this->getHeaders($tipe),
            'rows' => $this->getRows($tipe, $data),
        ]);

        $filename = strtolower(str_replace(' ', '_', $title)) . '_' . now()->format('d-m-Y') . '.pdf';

        return $pdf->download($filename);
    }

    private function colLetter(int $col): string
    {
        $letter = '';
        while ($col > 0) {
            $col--;
            $letter = chr(65 + ($col % 26)) . $letter;
            $col = intdiv($col, 26);
        }
        return $letter;
    }

    private function exportExcel($title, $headers, $rows, $tipe)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $lastCol = $this->colLetter(count($headers));

        // Title
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Date
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Dicetak: ' . now()->format('d/m/Y H:i'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        // Headers
        $headerRow = 4;
        foreach ($headers as $col => $header) {
            $coord = $this->colLetter($col + 1) . $headerRow;
            $sheet->setCellValue($coord, $header);
            $sheet->getStyle($coord)->getFont()->setBold(true);
            $sheet->getStyle($coord)->getFill()->setFillType('solid')->getStartColor()->setRGB('4472C4');
            $sheet->getStyle($coord)->getFont()->getColor()->setRGB('FFFFFF');
        }

        // Data
        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $coord = $this->colLetter($colIndex + 1) . ($headerRow + 1 + $rowIndex);
                $sheet->setCellValue($coord, $value);
            }
        }

        // Auto width
        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension($this->colLetter($i))->setAutoSize(true);
        }

        $filename = strtolower(str_replace(' ', '_', $title)) . '_' . now()->format('d-m-Y') . '.xls';

        $writer = new Xls($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }
}
