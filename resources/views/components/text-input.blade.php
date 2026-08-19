@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-[#B45F06] focus:border-[#B45F06] rounded-md shadow-sm']) }}>
