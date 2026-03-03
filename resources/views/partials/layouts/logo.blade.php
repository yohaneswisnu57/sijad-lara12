<div class="topbar-left text-center text-lg-left">
    <a href="{{ route('dashboard') }}" class="logo">
        {{--
            Crovex Template Logo Behavior:
            ┌──────────────────┬──────────────────────────────────────────────────┐
            │ Breakpoint       │ Yang tampil                                       │
            ├──────────────────┼──────────────────────────────────────────────────┤
            │ > 1024px (desk.) │ .logo-lg (logo horizontal penuh, logo-sm hidden) │
            │ ≤ 1024px (tab.)  │ .logo-sm (logo kecil, logo-lg hidden !important) │
            │ ≤ 768px (mobile) │ .logo-sm height:32px                             │
            └──────────────────┴──────────────────────────────────────────────────┘
        --}}

        {{-- Logo KECIL: tampil di tablet & mobile (≤ 1024px) --}}
        <span>
            <img src="{{ asset('assets/images/logo-sm-wm.png') }}"
                 alt="UKWMS"
                 class="logo-sm"
                 style="height:38px; width:auto; object-fit:contain;">
        </span>

        {{-- Logo BESAR horizontal: tampil di desktop (> 1024px) --}}
        <span>
            {{-- dark-topbar: pakai logo terang --}}
            <img src="{{ asset('assets/images/logo-wm-lg-full.png') }}"
                 alt="Universitas Katolik Widya Mandala Surabaya"
                 class="logo-lg logo-light"
                 style="height:42px; width:auto; object-fit:contain; max-width:200px;">
            {{-- default/light-topbar: pakai logo normal --}}
            <img src="{{ asset('assets/images/logo-wm-lg-full.png') }}"
                 alt="Universitas Katolik Widya Mandala Surabaya"
                 class="logo-lg"
                 style="height:42px; width:auto; object-fit:contain; max-width:200px;">
        </span>
    </a>
</div>
