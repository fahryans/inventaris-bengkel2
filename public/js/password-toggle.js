(function () {
    function init() {
        if (document.querySelector('[data-password-toggle]')) return;

        var style = document.createElement('style');
        style.textContent = [
            '.password-toggle-wrap{position:relative;width:100%}',
            '.password-toggle-wrap>input{padding-right:2.5rem!important}',
            '.password-toggle-btn{position:absolute;top:50%;right:.35rem;transform:translateY(-50%);width:2rem;height:2rem;display:flex;align-items:center;justify-content:center;border:0;background:none;cursor:pointer;color:#6c757d;z-index:5;border-radius:.25rem}',
            '.password-toggle-btn:hover{color:#343a40}'
        ].join('\n');
        document.head.appendChild(style);

        var EYE = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
        var EYE_OFF = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

        document.querySelectorAll('input[type="password"]').forEach(function (input) {
            if (input.closest('.password-toggle-wrap')) return;

            var wrap = document.createElement('div');
            wrap.className = 'password-toggle-wrap';
            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(input);

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'password-toggle-btn';
            btn.setAttribute('aria-label', 'Tampilkan password');
            btn.setAttribute('data-password-toggle', '');
            btn.innerHTML = EYE;
            btn.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.innerHTML = show ? EYE_OFF : EYE;
                btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
            });
            wrap.appendChild(btn);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();