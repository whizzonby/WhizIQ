<x-layouts.app>
<x-slot name="title">Business Management Software for Freelancers & Small Teams</x-slot>

<style>
/* ─── Scroll reveal ─────────────────────────────────── */
.reveal{opacity:0;transform:translateY(24px);transition:opacity .65s cubic-bezier(.16,1,.3,1),transform .65s cubic-bezier(.16,1,.3,1)}
.reveal.in{opacity:1;transform:none}
.reveal.d1{transition-delay:.1s}
.reveal.d2{transition-delay:.2s}
.reveal.d3{transition-delay:.3s}
.reveal.d4{transition-delay:.4s}

/* ─── Hero background ───────────────────────────────── */
.hero-bg{
    background:
        radial-gradient(ellipse 90% 60% at 50% -10%, rgba(124,58,237,.32) 0%, transparent 65%),
        radial-gradient(ellipse 40% 40% at 80% 70%, rgba(79,70,229,.18) 0%, transparent 55%),
        linear-gradient(rgba(124,58,237,.07) 1px, transparent 1px) 0 0 / 52px 52px,
        linear-gradient(90deg, rgba(124,58,237,.07) 1px, transparent 1px) 0 0 / 52px 52px,
        #060412;
}
.cta-bg{
    background:
        radial-gradient(ellipse 80% 60% at 50% 110%, rgba(124,58,237,.35) 0%, transparent 60%),
        radial-gradient(ellipse 50% 40% at 10% 30%, rgba(79,70,229,.2) 0%, transparent 55%),
        linear-gradient(rgba(124,58,237,.07) 1px, transparent 1px) 0 0 / 52px 52px,
        linear-gradient(90deg, rgba(124,58,237,.07) 1px, transparent 1px) 0 0 / 52px 52px,
        #060412;
}

/* ─── Gradient text ─────────────────────────────────── */
.grad-text{
    background:linear-gradient(120deg,#e879f9 0%,#a78bfa 45%,#818cf8 100%);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.grad-text-warm{
    background:linear-gradient(120deg,#fb923c 0%,#f472b6 50%,#a78bfa 100%);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}

/* ─── Mock browser window ───────────────────────────── */
.app-win{
    background:#0d0a1e;
    border-radius:1.25rem;
    border:1px solid rgba(255,255,255,.09);
    overflow:hidden;
    box-shadow:
        0 0 0 1px rgba(124,58,237,.12),
        0 32px 80px rgba(0,0,0,.38),
        0 8px 32px rgba(0,0,0,.24);
}
.win-bar{
    background:#100e22;
    border-bottom:1px solid rgba(255,255,255,.07);
    padding:12px 18px;
    display:flex;
    align-items:center;
    gap:10px;
}
.win-dot{width:11px;height:11px;border-radius:50%;}
.win-url{
    background:#09071c;
    border:1px solid rgba(255,255,255,.07);
    border-radius:6px;
    padding:3px 14px;
    font-size:11px;
    font-family:ui-monospace,monospace;
    color:rgba(255,255,255,.22);
}

/* ─── Floating cards ────────────────────────────────── */
@keyframes float-a{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
@keyframes float-b{0%,100%{transform:translateY(-6px)}50%{transform:translateY(6px)}}
@keyframes float-c{0%,100%{transform:translateY(4px)}50%{transform:translateY(-8px)}}
.float-a{animation:float-a 3.8s ease-in-out infinite;}
.float-b{animation:float-b 4.4s ease-in-out infinite;}
.float-c{animation:float-c 3.2s ease-in-out infinite;}

/* ─── Tilt mockup ───────────────────────────────────── */
.tilt-wrap{
    transform:perspective(1400px) rotateX(3deg) scale(.97);
    transition:transform .5s ease;
}
.tilt-wrap:hover{transform:perspective(1400px) rotateX(0deg) scale(1);}

/* ─── Card hover lift ───────────────────────────────── */
.card-lift{transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease;}
.card-lift:hover{transform:translateY(-4px);box-shadow:0 16px 48px rgba(124,58,237,.12);border-color:#c4b5fd !important;}

/* ─── Section label ─────────────────────────────────── */
.sec-label{
    display:inline-flex;align-items:center;gap:8px;
    font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.16em;color:#7c3aed;
    margin-bottom:1.25rem;
}
.sec-label::before{
    content:'';display:block;width:18px;height:1.5px;background:#7c3aed;border-radius:2px;
}

/* ─── Check bullet ──────────────────────────────────── */
.check-li{display:flex;align-items:flex-start;gap:14px;margin-bottom:1.35rem;}
.check-icon{
    width:22px;height:22px;border-radius:50%;background:#f3eeff;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;
}

/* ─── FAQ ───────────────────────────────────────────── */
.faq-item{border-bottom:1px solid rgba(255,255,255,.06);}
.faq-item:last-child{border-bottom:none;}
.faq-q{
    width:100%;display:flex;align-items:center;justify-content:space-between;
    text-align:left;padding:1.6rem 2rem;cursor:pointer;
    background:transparent;border:none;gap:1.5rem;
    transition:background .2s ease;
}
.faq-q:hover{background:rgba(124,58,237,.04);}
.faq-icon{
    width:32px;height:32px;border-radius:50%;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;
    background:rgba(124,58,237,.12);border:1px solid rgba(124,58,237,.22);
    transition:background .2s ease,border-color .2s ease;
}
.faq-q:hover .faq-icon{background:rgba(124,58,237,.22);border-color:rgba(124,58,237,.4);}

/* ─── FAQ number badge ──────────────────────────────── */
.faq-num{
    min-width:28px;height:28px;border-radius:8px;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;
    font-size:.7rem;font-weight:800;font-family:ui-monospace,monospace;
    background:rgba(124,58,237,.1);color:rgba(167,139,250,.6);
    border:1px solid rgba(124,58,237,.15);
}

/* ─── Pulse badge ───────────────────────────────────── */
@keyframes pulse-ring{0%,100%{box-shadow:0 0 0 0 rgba(167,139,250,.6)}65%{box-shadow:0 0 0 8px rgba(167,139,250,0)}}
.pulse-dot{animation:pulse-ring 2.5s ease-in-out infinite;}

/* ─── Announcement slide ────────────────────────────── */
.ann-slide{
    transition:max-height .3s ease, opacity .3s ease, padding .3s ease;
    overflow:hidden;
}
</style>

{{-- ══════════════════════════════════════════════════════════
     0 · ANNOUNCEMENT BAR  (dismissible · session-gated)
     ══════════════════════════════════════════════════════════ --}}
<div x-data="{ visible: !sessionStorage.getItem('wiq_ann_v2') }"
     x-show="visible"
     x-transition:leave="transition duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="relative text-sm font-medium text-white"
     style="background:linear-gradient(90deg,#6d28d9 0%,#4f46e5 100%);">
    <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-center gap-3 text-center">
        <svg class="w-4 h-4 text-yellow-300 flex-shrink-0 hidden sm:block" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/>
        </svg>
        <span class="font-medium" style="color:rgba(255,255,255,.9);">
            🎉&nbsp; Launch offer 14 days free on every plan. No credit card required.
        </span>
        <a href="{{ route('register') }}"
           class="font-bold whitespace-nowrap underline underline-offset-2 hover:opacity-80 transition-opacity ml-1">
            Claim your trial →
        </a>
    </div>
    <button @click="visible=false; sessionStorage.setItem('wiq_ann_v2','1')"
            class="absolute right-4 top-1/2 -translate-y-1/2 p-1.5 rounded-lg opacity-50 hover:opacity-100 transition-opacity">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>


{{-- ══════════════════════════════════════════════════════════
     1 · HERO
     ══════════════════════════════════════════════════════════ --}}
<section class="hero-bg" style="padding:120px 0 180px;">
<div class="max-w-7xl mx-auto px-6 text-center">

    {{-- Badge --}}
    <div class="reveal inline-flex items-center gap-2.5 mb-14"
         style="border-radius:9999px;padding:13px 28px;background:rgba(139,92,246,0.25);border:1.5px solid rgba(139,92,246,0.8);">
        <span class="pulse-dot w-2 h-2 rounded-full flex-shrink-0" style="background:#a78bfa;"></span>
        <span style="font-size:.9rem;font-weight:600;color:#fff;line-height:1;">Free for 14 days · No credit card needed</span>
    </div>

    {{-- Headline --}}
    <h1 class="reveal d1 text-white tracking-[-0.04em]"
        style="font-size:clamp(2.6rem,5.5vw,4.8rem);line-height:1.0;font-weight:950;">
        Manage your clients, your money,<br>
        <span class="grad-text">and your business all from WhizziQ.</span>
    </h1>

    {{-- Sub --}}
    <p class="reveal d2 mx-auto text-xl leading-relaxed" style="color:rgba(255,255,255,.68);max-width:580px;line-height:1.8;margin-top:2rem;">
        CRM, Invoicing, Booking, Tasks, Financial health. All. One tool. One subscription.
    </p>

    {{-- CTAs --}}
    <div class="reveal d3 flex flex-wrap items-center justify-center gap-4" style="margin-top:3rem;">
        <a href="{{ route('register') }}"
           class="inline-flex items-center gap-2.5 text-white font-bold text-base rounded-2xl whitespace-nowrap transition-all hover:opacity-92 hover:-translate-y-0.5 active:translate-y-0"
           style="padding:18px 42px;background:linear-gradient(135deg,#7c3aed 0%,#4f46e5 100%);box-shadow:0 0 0 1px rgba(124,58,237,.6),0 10px 40px rgba(124,58,237,.48);">
            Start for free
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
        <a href="#how-it-works"
           class="inline-flex items-center gap-2 font-semibold text-base whitespace-nowrap rounded-2xl border transition-all hover:bg-white/5"
           style="padding:18px 32px;color:rgba(255,255,255,.55);border-color:rgba(255,255,255,.14);">
            See how it works
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </a>
    </div>
    <p class="reveal d3 text-xs" style="color:rgba(255,255,255,.22);margin-top:1.25rem;">
        No credit card · Cancel anytime · Ready in 10 minutes
    </p>

    {{-- Dashboard mockup + floating cards --}}
    <div class="reveal d4 relative" style="margin-top:5rem;padding:0 1rem;">

        {{-- Floating card: top-left --}}
        <div class="float-a absolute z-20 hidden lg:block" style="top:-2rem;left:-1rem;">
            <div class="rounded-2xl px-6 py-4 flex items-center gap-4"
                 style="background:rgba(13,10,30,.96);border:1px solid rgba(52,211,153,.28);backdrop-filter:blur(16px);box-shadow:0 12px 40px rgba(0,0,0,.5);">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:rgba(52,211,153,.14);">
                    <svg class="w-5 h-5" style="color:#34d399;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="text-left">
                    <p style="font-size:11px;font-weight:700;color:#34d399;margin-bottom:2px;letter-spacing:.04em;">Revenue ↑ 14%</p>
                    <p class="text-white font-black" style="font-size:1.3rem;line-height:1;letter-spacing:-.03em;">£9,840</p>
                    <p style="font-size:10px;color:rgba(255,255,255,.38);margin-top:3px;">This month</p>
                </div>
            </div>
        </div>

        {{-- Floating card: top-right --}}
        <div class="float-b absolute z-20 hidden lg:block" style="top:-1rem;right:-1rem;">
            <div class="rounded-2xl px-6 py-4 flex items-center gap-4"
                 style="background:rgba(13,10,30,.96);border:1px solid rgba(96,165,250,.28);backdrop-filter:blur(16px);box-shadow:0 12px 40px rgba(0,0,0,.5);">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:rgba(96,165,250,.14);">
                    <svg class="w-5 h-5" style="color:#60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="text-left">
                    <p style="font-size:11px;font-weight:700;color:#60a5fa;margin-bottom:2px;letter-spacing:.04em;">New booking</p>
                    <p class="text-white font-bold" style="font-size:.95rem;line-height:1.3;">Strategy Call</p>
                    <p style="font-size:10px;color:rgba(255,255,255,.38);margin-top:3px;">Today · 10:00 AM</p>
                </div>
            </div>
        </div>

        {{-- Floating card: bottom-right --}}
        <div class="float-c absolute z-20 hidden lg:block" style="bottom:-2.5rem;right:4rem;">
            <div class="rounded-2xl px-6 py-4 flex items-center gap-4"
                 style="background:rgba(13,10,30,.96);border:1px solid rgba(167,139,250,.28);backdrop-filter:blur(16px);box-shadow:0 12px 40px rgba(0,0,0,.5);">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:rgba(52,211,153,.14);">
                    <svg class="w-5 h-5" style="color:#34d399;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-left">
                    <p style="font-size:11px;font-weight:700;color:#34d399;margin-bottom:2px;letter-spacing:.04em;">Invoice PAID</p>
                    <p class="text-white font-black" style="font-size:1.3rem;line-height:1;letter-spacing:-.03em;">£3,800</p>
                    <p style="font-size:10px;color:rgba(255,255,255,.38);margin-top:3px;">Acme Design Co.</p>
                </div>
            </div>
        </div>

        {{-- Browser mockup --}}
        <div class="tilt-wrap">
        <div class="app-win">
            <div class="win-bar" style="padding:14px 22px;">
                <div style="display:flex;gap:7px;">
                    <div class="win-dot" style="background:#ff5f57;"></div>
                    <div class="win-dot" style="background:#febc2e;"></div>
                    <div class="win-dot" style="background:#28c840;"></div>
                </div>
                <div class="win-url" style="font-size:12px;padding:4px 18px;">app.whiziq.com/dashboard</div>
            </div>
            <div class="flex" style="min-height:500px;">

                {{-- Sidebar --}}
                <div class="hidden md:flex flex-col gap-1 flex-shrink-0"
                     style="width:210px;border-right:1px solid rgba(255,255,255,.06);background:#100e22;padding:20px 12px;">
                    <div class="flex items-center gap-3 px-3 py-3 mb-4">
                        <div class="w-7 h-7 rounded-lg flex-shrink-0"
                             style="background:linear-gradient(135deg,#7c3aed,#4f46e5);"></div>
                        <span class="font-black text-white" style="font-size:.9rem;">WhizziQ</span>
                    </div>
                    @foreach([
                        ['Dashboard', true,  'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ['Clients',   false, 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                        ['Money',     false, 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['Work',      false, 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                        ['Settings',  false, 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                    ] as [$item, $on, $path])
                    <div class="flex items-center gap-3 px-3 py-3 rounded-xl font-semibold cursor-pointer"
                         style="{{ $on ? 'background:rgba(124,58,237,.22);color:#c4b5fd;font-size:.82rem;' : 'color:rgba(255,255,255,.28);font-size:.82rem;' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $on ? '2.2' : '1.8' }}" d="{{ $path }}"/>
                        </svg>
                        {{ $item }}
                    </div>
                    @endforeach
                </div>

                {{-- Main content --}}
                <div class="flex-1" style="background:#0d0a1e;padding:28px;">

                    {{-- Top bar --}}
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <p class="text-white font-bold" style="font-size:.95rem;">Good morning, Alex 👋</p>
                            <p style="color:rgba(255,255,255,.3);font-size:.75rem;margin-top:3px;">3 items need your attention today</p>
                        </div>
                        <span class="rounded-full font-bold px-5 py-2"
                              style="font-size:.75rem;background:rgba(124,58,237,.15);color:#c4b5fd;border:1px solid rgba(124,58,237,.25);">Apr 2026</span>
                    </div>

                    {{-- Stat cards --}}
                    <div class="grid grid-cols-4 gap-4 mb-6">
                        @foreach([
                            ['Revenue',  '£9,840', '↑ 14%',      'rgba(52,211,153,.09)', 'rgba(52,211,153,.25)', '#34d399'],
                            ['Unpaid',   '£3,200', '2 invoices',  'rgba(251,146,60,.09)', 'rgba(251,146,60,.25)', '#fb923c'],
                            ['Booked',   '18',     'This week',   'rgba(96,165,250,.09)', 'rgba(96,165,250,.25)', '#60a5fa'],
                            ['Pipeline', '£41k',   '7 deals',     'rgba(167,139,250,.09)','rgba(167,139,250,.25)','#a78bfa'],
                        ] as [$l,$v,$s,$bg,$bd,$c])
                        <div class="rounded-2xl border" style="background:{{ $bg }};border-color:{{ $bd }};padding:16px 18px;">
                            <p style="color:rgba(255,255,255,.32);font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;">{{ $l }}</p>
                            <p class="text-white font-black leading-none" style="font-size:1.4rem;letter-spacing:-.03em;margin-bottom:6px;">{{ $v }}</p>
                            <p style="color:{{ $c }};font-size:.7rem;font-weight:700;">{{ $s }}</p>
                        </div>
                        @endforeach
                    </div>

                    {{-- Revenue chart --}}
                    <div class="rounded-2xl border mb-5" style="background:rgba(255,255,255,.02);border-color:rgba(255,255,255,.06);padding:20px 22px;">
                        <div class="flex items-center justify-between mb-4">
                            <p style="color:rgba(255,255,255,.4);font-size:.75rem;font-weight:600;">Revenue April 2026</p>
                            <span style="color:#34d399;font-size:.75rem;font-weight:700;">↑ 14% vs March</span>
                        </div>
                        <div class="flex items-end gap-1.5" style="height:80px;">
                            @foreach([14,22,18,34,28,24,42,46,40,54,48,68,88] as $i => $h)
                            <div class="flex-1 rounded-t"
                                 style="height:{{ $h }}%;background:{{ $i>=11 ? 'linear-gradient(to top,#7c3aed,#a78bfa)' : 'rgba(124,58,237,.18)' }};"></div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Recent activity row --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-2xl border" style="background:rgba(255,255,255,.02);border-color:rgba(255,255,255,.06);padding:16px 18px;">
                            <p style="color:rgba(255,255,255,.3);font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;">Upcoming</p>
                            @foreach([['Strategy Call','10:00 AM','#60a5fa'],['Design Review','2:30 PM','#a78bfa']] as [$ev,$t,$c])
                            <div class="flex items-center justify-between mb-2">
                                <p style="font-size:.75rem;color:rgba(255,255,255,.7);">{{ $ev }}</p>
                                <span style="font-size:.65rem;font-weight:700;color:{{ $c }};">{{ $t }}</span>
                            </div>
                            @endforeach
                        </div>
                        <div class="rounded-2xl border" style="background:rgba(255,255,255,.02);border-color:rgba(255,255,255,.06);padding:16px 18px;">
                            <p style="color:rgba(255,255,255,.3);font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;">Needs attention</p>
                            @foreach([['Invoice overdue','Acme Corp','#fb923c'],['Deal going cold','Nexus Ltd','#a78bfa']] as [$tag,$co,$c])
                            <div class="flex items-center justify-between mb-2">
                                <p style="font-size:.75rem;color:rgba(255,255,255,.7);">{{ $co }}</p>
                                <span style="font-size:.65rem;font-weight:700;color:{{ $c }};">{{ $tag }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </div>
        </div>
    </div>

</div>
</section>


{{-- ══════════════════════════════════════════════════════════
     2 · WHO IT'S FOR (marquee strip)
     ══════════════════════════════════════════════════════════ --}}
<section style="background:linear-gradient(135deg,#0a0a0a 0%,#111111 40%,#0d0d0d 100%);padding:52px 0;overflow:hidden;">

    {{-- Label --}}
    <p class="text-center mb-8 font-bold uppercase tracking-widest"
       style="font-size:.7rem;letter-spacing:.22em;background:linear-gradient(90deg,#6b7280,#d1d5db,#9ca3af);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
        Built for independent businesses
    </p>

    {{-- Marquee track --}}
    <div class="relative" style="mask-image:linear-gradient(90deg,transparent 0%,black 12%,black 88%,transparent 100%);-webkit-mask-image:linear-gradient(90deg,transparent 0%,black 12%,black 88%,transparent 100%);">
        <div class="flex gap-4" style="animation:marquee 28s linear infinite;width:max-content;">

            @php
            $items = [
                ['🎨', 'Design Studios'],
                ['💼', 'Consultants'],
                ['📸', 'Photographers'],
                ['🏢', 'Agencies'],
                ['🎯', 'Coaches & Trainers'],
                ['✍️', 'Freelancers'],
                ['⚖️', 'Legal Professionals'],
                ['🏥', 'Health Practitioners'],
                ['🛠️', 'Contractors'],
                ['🎵', 'Creative Professionals'],
                ['🧘', 'Wellness Coaches'],
                ['📐', 'Architects & Designers'],
            ];
            @endphp

            {{-- First copy --}}
            @foreach($items as [$icon, $label])
            <span class="inline-flex items-center gap-2.5 whitespace-nowrap font-bold rounded-full"
                  style="padding:11px 22px;font-size:.88rem;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:rgba(200,200,200,.85);letter-spacing:.01em;">
                <span>{{ $icon }}</span>
                <span style="background:linear-gradient(90deg,#c0c0c0,#e8e8e8,#a8a8a8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $label }}</span>
            </span>
            @endforeach

            {{-- Duplicate for seamless loop --}}
            @foreach($items as [$icon, $label])
            <span class="inline-flex items-center gap-2.5 whitespace-nowrap font-bold rounded-full"
                  style="padding:11px 22px;font-size:.88rem;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:rgba(200,200,200,.85);letter-spacing:.01em;">
                <span>{{ $icon }}</span>
                <span style="background:linear-gradient(90deg,#c0c0c0,#e8e8e8,#a8a8a8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">{{ $label }}</span>
            </span>
            @endforeach

        </div>
    </div>

</section>

<style>
@keyframes marquee {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
</style>


{{-- ══════════════════════════════════════════════════════════
     3 · PROBLEM
     ══════════════════════════════════════════════════════════ --}}
<style>
@keyframes pain-pulse {
    0%,100% { opacity:.06; transform:scale(1);   }
    50%      { opacity:.18; transform:scale(1.2); }
}
.pain-glow { animation: pain-pulse 4s ease-in-out infinite; }
.pain-glow.d1 { animation-delay:0s;    }
.pain-glow.d2 { animation-delay:.7s;   }
.pain-glow.d3 { animation-delay:1.4s;  }
.pain-glow.d4 { animation-delay:2.1s;  }
.pain-glow.d5 { animation-delay:2.8s;  }
.pain-glow.d6 { animation-delay:3.5s;  }

.pain-card {
    background: rgba(255,255,255,.025);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 1.25rem;
    padding: 2.25rem 2rem;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform .28s ease, border-color .28s ease, box-shadow .28s ease;
}
.pain-card:hover {
    transform: translateY(-5px);
}
</style>

<section style="background:#07050f;padding:128px 0 136px;position:relative;overflow:hidden;">

    {{-- Background decoration --}}
    <div style="position:absolute;inset:0;background:linear-gradient(rgba(124,58,237,.04) 1px,transparent 1px) 0 0/52px 52px,linear-gradient(90deg,rgba(124,58,237,.04) 1px,transparent 1px) 0 0/52px 52px;pointer-events:none;"></div>
    <div style="position:absolute;top:-100px;left:50%;transform:translateX(-50%);width:900px;height:520px;background:radial-gradient(ellipse,rgba(124,58,237,.12) 0%,transparent 65%);pointer-events:none;"></div>
    <div style="position:absolute;bottom:-60px;left:12%;width:420px;height:320px;background:radial-gradient(ellipse,rgba(79,70,229,.07) 0%,transparent 70%);pointer-events:none;"></div>
    <div style="position:absolute;bottom:-40px;right:8%;width:380px;height:280px;background:radial-gradient(ellipse,rgba(244,114,182,.05) 0%,transparent 70%);pointer-events:none;"></div>

    <div class="max-w-5xl mx-auto px-6 relative">

        {{-- Header --}}
        <div class="text-center reveal" style="margin-bottom:5rem;">
            <span class="sec-label" style="color:#a78bfa;display:inline-flex;justify-content:center;margin-bottom:1.5rem;">The real problem</span>
            <h2 class="font-black text-white" style="font-size:clamp(2.6rem,5.5vw,4.2rem);letter-spacing:-.04em;line-height:1.08;">
                Running your business<br>
                <span class="grad-text">shouldn't feel like this.</span>
            </h2>
            <p class="mx-auto" style="color:rgba(255,255,255,.42);font-size:1.08rem;max-width:500px;line-height:1.9;margin-top:1.75rem;">
                Every independent operator knows the feeling. These are the problems we hear every single day and built WhizziQ to fix.
            </p>
        </div>

        {{-- Pain point cards --}}
        @php
        $pains = [
            ['#fb923c','d1','01','Clients slip away without a word',     'You worked hard to win them. But without something watching your relationships, follow-ups fall through the cracks and clients quietly drift to someone else.'],
            ['#f87171','d2','02','Chasing payments is a second job',     'You did the work and sent the invoice. Now you\'re spending your evenings on awkward reminders because cash flow can\'t wait and no one should have to beg to get paid.'],
            ['#60a5fa','d3','03','Admin is stealing your best hours',    'Scheduling, note-taking, reminders, follow-ups each feels small until you add them up. Solo operators lose entire days every week to work that shouldn\'t need a person.'],
            ['#34d399','d4','04','No real picture of your finances',     'Money comes in, money goes out. But after expenses, unpaid invoices, and upcoming tax, is the business actually healthy? Most small business owners are genuinely guessing.'],
            ['#a78bfa','d5','05','One missed follow-up costs a deal',    'A hot lead goes quiet for two weeks. A renewal date slips by unnoticed. When everything critical lives only in your head, the cracks are invisible until the damage is done.'],
            ['#f472b6','d6','06','More clients just means more chaos',   'Every new booking, every new deal adds more to track, more to remember, more to juggle. Winning more business starts to feel like punishment instead of progress.'],
        ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3" style="gap:1.25rem;">
            @foreach($pains as [$c, $delay, $num, $title, $desc])
            <div class="reveal pain-card">

                {{-- Animated glow blob --}}
                <div class="pain-glow {{ $delay }}"
                     style="position:absolute;top:-30px;right:-30px;width:130px;height:130px;border-radius:50%;background:{{ $c }};filter:blur(36px);pointer-events:none;"></div>

                {{-- Large muted number --}}
                <div style="position:absolute;top:1.25rem;right:1.5rem;font-size:4.5rem;font-weight:900;line-height:1;letter-spacing:-.06em;color:{{ $c }};opacity:.06;pointer-events:none;user-select:none;">{{ $num }}</div>

                {{-- Content --}}
                <div style="position:relative;padding-top:.25rem;">
                    <h3 class="font-bold text-white"
                        style="font-size:1.08rem;letter-spacing:-.02em;line-height:1.45;margin-bottom:1.1rem;">{{ $title }}</h3>
                    <p style="color:rgba(255,255,255,.4);font-size:.9rem;line-height:1.9;">{{ $desc }}</p>
                </div>

                {{-- Bottom accent --}}
                <div style="margin-top:auto;padding-top:1.75rem;">
                    <div style="height:2px;width:2.5rem;border-radius:999px;background:{{ $c }};opacity:.4;"></div>
                </div>

            </div>
            @endforeach
        </div>

        {{-- Resolution pill --}}
        <div class="reveal text-center" style="margin-top:5rem;">
            <div class="inline-flex items-center gap-3 px-7 py-3.5 rounded-2xl"
                 style="background:rgba(124,58,237,.1);border:1px solid rgba(124,58,237,.25);">
                <svg class="w-4 h-4 flex-shrink-0" style="color:#a78bfa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/>
                </svg>
                <p style="color:rgba(255,255,255,.8);font-size:1.15rem;font-weight:600;">
                    WhizziQ is built to solve exactly this
                    <a href="{{ route('register') }}" style="color:#c4b5fd;font-weight:800;"
                       class="hover:opacity-80 transition-opacity">start free today →</a>
                </p>
            </div>
        </div>

    </div>
</section>


{{-- ══════════════════════════════════════════════════════════
     4 · A DAY WITH WHIZIQ (tabbed timeline)
     ══════════════════════════════════════════════════════════ --}}
<section style="background:#0e0c1e;padding:120px 0;" id="how-it-works">
<div class="max-w-3xl mx-auto px-6">

    {{-- Header --}}
    <div class="text-center reveal" style="margin-bottom:3rem;">
        <div style="display:inline-flex;justify-content:center;margin-bottom:1.25rem;font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#a78bfa;">A day with WhizziQ</div>
        <h2 class="font-black leading-tight" style="font-size:clamp(2rem,3.8vw,3rem);letter-spacing:-.035em;color:#fff;">
            See what your day looks like<br>
            <span style="background:linear-gradient(135deg,#a78bfa,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">when everything just works.</span>
        </h2>
        <p style="color:rgba(255,255,255,.5);font-size:1rem;margin-top:1.25rem;">
            Pick your business type.
        </p>
    </div>

    {{-- Tabbed timeline --}}
    <div x-data="{ tab: 'beautician' }">

        {{-- Tab pills --}}
        <div class="flex flex-wrap justify-center" style="gap:.75rem;margin-bottom:1.25rem;">
            @foreach([
                ['beautician', '💅', 'Beautician'],
                ['lawyer',     '⚖️',  'Lawyer'],
                ['cleaning',   '🧹', 'Cleaning Service'],
                ['consultant', '📋', 'Consultant'],
            ] as [$key, $icon, $label])
            <button
                @click="tab = '{{ $key }}'"
                :style="tab === '{{ $key }}'
                    ? 'border-radius:9999px;padding:13px 28px;display:inline-flex;align-items:center;gap:9px;font-size:.9rem;font-family:inherit;cursor:pointer;transition:all .2s ease;line-height:1;white-space:nowrap;outline:none;background:rgba(139,92,246,0.25);color:#fff;border:1.5px solid rgba(139,92,246,0.8);font-weight:600;'
                    : 'border-radius:9999px;padding:13px 28px;display:inline-flex;align-items:center;gap:9px;font-size:.9rem;font-family:inherit;cursor:pointer;transition:all .2s ease;line-height:1;white-space:nowrap;outline:none;background:transparent;color:rgba(255,255,255,0.52);border:1.5px solid rgba(255,255,255,0.18);font-weight:400;'">
                <span style="font-size:1rem;line-height:1;">{{ $icon }}</span><span>{{ $label }}</span>
            </button>
            @endforeach
        </div>

        {{-- Disclaimer --}}
        <p class="text-center" style="color:rgba(255,255,255,.5);font-size:.95rem;margin-bottom:2.75rem;">
            Just a few examples, WhizziQ works for any service-based business.
        </p>

        {{-- ── DATA ── --}}
        @php
        $tabs = [
            'beautician' => [
                ['#a78bfa', '7:00 AM',  'Your day is already mapped out',       'WhizziQ flags that today\'s bookings total £380 in expected revenue. One client is a repeat with a gap since her last visit. You\'re ready before anyone walks through the door.'],
                ['#60a5fa', '9:30 AM',  'Client pays before she leaves',         'After the appointment, you tap "Send Invoice" and a Stripe payment link goes straight to her phone. She pays in under a minute. No card machine awkwardness. No chasing.'],
                ['#34d399', '1:00 PM',  'Aftercare sent, review requested',      'Your aftercare message goes out automatically an hour after the appointment. WhizziQ follows up with a gentle review nudge. You\'re building trust while doing your next client.'],
                ['#fb923c', '4:00 PM',  'Quiet month? WhizziQ flags it early',  'Revenue is 22% below last month at this point. WhizziQ surfaces the gap so you can post a flash offer, nudge lapsed clients, or push your booking link, before the month is lost.'],
                ['#f472b6', '6:00 PM',  'Day done, everything accounted for',   'Every appointment logged. Every payment tracked. Your CRM updated. Tomorrow\'s schedule visible. You close the app knowing nothing slipped through.'],
            ],
            'lawyer' => [
                ['#a78bfa', '8:00 AM',  'Pipeline at a glance before court',          'WhizziQ shows three active matters in different stages, one awaiting documents, one in negotiation, one ready to close. You know exactly where to focus before the day starts.'],
                ['#60a5fa', '10:00 AM', 'Consultation booked, billable hours started', 'A new client books a consultation via your link. WhizziQ logs the intake automatically and starts a matter file. You open the session already knowing their context.'],
                ['#34d399', '2:00 PM',  'Invoice raised in under 30 seconds',          'Matter closed. You open WhizziQ, select the client, add your time entries, and send. The invoice goes out with a payment link. No spreadsheets. No forgotten billable hours.'],
                ['#fb923c', '3:30 PM',  'New enquiry captured, follow-up scheduled',   'A prospect submits a form on your booking page. WhizziQ creates a contact, adds them to your pipeline, and sets a follow-up task for tomorrow morning automatically.'],
                ['#f472b6', '6:00 PM',  'Retainer clients visible at a glance',        'Your dashboard shows retainer clients by status, hours used, and renewal dates. Nothing falls through the cracks. You head home with a clear picture of next week.'],
            ],
            'cleaning' => [
                ['#a78bfa', '7:00 AM',   'Jobs assigned, team notified',                    'Your crew\'s schedule is set. WhizziQ shows which jobs are confirmed, which clients are recurring, and where the day starts. Everyone knows where to be before you say a word.'],
                ['#60a5fa', '10:00 AM',  'Job done, invoice sent automatically',            'The team marks a job complete. WhizziQ generates and sends the invoice to the client instantly. No manual write-ups. No end-of-day admin pile.'],
                ['#34d399', '12:30 PM',  'Payment received, receipt sent, rebook prompted', 'The client pays online. They receive a receipt and a soft nudge to book their next clean. Your recurring revenue builds itself in the background.'],
                ['#fb923c', '3:00 PM',   'Recurring client books their next slot online',   'A client you haven\'t seen in six weeks books a one-off through your link. WhizziQ adds them back to the pipeline and flags the opportunity to convert them to a recurring plan.'],
                ['#f472b6', '6:00 PM',   'All jobs invoiced, no loose ends',                'Every completed job has an invoice. Every invoice has a status. Outstanding payments are flagged. You\'re not chasing tomorrow what could be paid today.'],
            ],
            'consultant' => [
                ['#a78bfa', '8:00 AM',  'Three open bids, one needs attention',         'WhizziQ shows your active proposals with last-contact dates. One prospect hasn\'t responded in nine days. You send a brief follow-up before your first meeting. That\'s the one that closes.'],
                ['#60a5fa', '10:30 AM', 'Discovery call booked, scope captured',         'A new lead books a discovery call through your link. You use the pre-call notes WhizziQ gathered from their intake form to walk in prepared. Scope and timeline agreed by end of call.'],
                ['#34d399', '2:00 PM',  'Deposit invoice sent, project started',         'Engagement confirmed. You raise a 50% deposit invoice in WhizziQ, attach your terms, and send. The client pays the same afternoon. The project is live before the day ends.'],
                ['#fb923c', '4:30 PM',  'Completion certificate and final invoice out',  'Deliverables approved. WhizziQ sends the completion summary and final invoice together. Professional close, clean paper trail, and funds on the way.'],
                ['#f472b6', '6:00 PM',  'Pipeline and forecast updated',                 'Your dashboard shows the closed deal, updated forecast, and two new prospects in the pipeline. You know your next 60 days of revenue at a glance. No guessing.'],
            ],
        ];
        @endphp

        @foreach($tabs as $tabKey => $timeline)
        <div x-show="tab === '{{ $tabKey }}'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0">
        <div class="relative">

            {{-- Vertical line --}}
            <div class="absolute" style="left:.6875rem;top:1.5rem;bottom:1.5rem;width:1px;background:linear-gradient(to bottom,transparent 0%,rgba(167,139,250,.25) 8%,rgba(167,139,250,.25) 92%,transparent 100%);"></div>

            <div style="display:flex;flex-direction:column;gap:1.5rem;">
            @foreach($timeline as [$c, $time, $title, $desc])
            <div class="flex" style="gap:1.75rem;align-items:flex-start;">

                {{-- Dot --}}
                <div style="width:1.375rem;flex-shrink:0;display:flex;justify-content:center;padding-top:.35rem;">
                    <div style="width:13px;height:13px;border-radius:50%;background:{{ $c }};box-shadow:0 0 0 3px #0e0c1e,0 0 0 5px {{ $c }};flex-shrink:0;position:relative;z-index:1;"></div>
                </div>

                {{-- Card --}}
                <div class="flex-1 rounded-2xl"
                     style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-left:3px solid {{ $c }};padding:1.5rem 1.75rem 1.625rem;">

                    {{-- Time badge --}}
                    <span class="inline-block text-xs font-bold rounded-full"
                          style="padding:4px 13px;background:{{ $c }}22;color:{{ $c }};border:1px solid {{ $c }}55;letter-spacing:.05em;margin-bottom:1rem;">
                        {{ $time }}
                    </span>

                    <h3 class="font-bold" style="color:#fff;font-size:1.05rem;letter-spacing:-.015em;line-height:1.4;margin-bottom:.625rem;">{{ $title }}</h3>
                    <p style="color:rgba(255,255,255,.55);font-size:.9rem;line-height:1.85;">{{ $desc }}</p>

                </div>

            </div>
            @endforeach
            </div>

        </div>
        </div>
        @endforeach

    </div>

</div>
</section>


{{-- ══════════════════════════════════════════════════════════
     5 · FEATURE: CRM & PIPELINE
     ══════════════════════════════════════════════════════════ --}}
<section style="background:#f9f7ff;padding:136px 0;" id="features">
<div class="max-w-6xl mx-auto px-6">
<div class="grid grid-cols-1 lg:grid-cols-2 items-center" style="gap:3rem;">

    {{-- Mock window --}}
    <div class="reveal">
    <div class="app-win">
        <div class="win-bar" style="padding:14px 22px;">
            <div style="display:flex;gap:7px;">
                <div class="win-dot" style="background:#ff5f57;"></div>
                <div class="win-dot" style="background:#febc2e;"></div>
                <div class="win-dot" style="background:#28c840;"></div>
            </div>
            <div class="win-url" style="font-size:11px;padding:4px 18px;">app.whiziq.com/pipeline</div>
        </div>

        {{-- Tab bar --}}
        <div style="background:#100e22;border-bottom:1px solid rgba(255,255,255,.06);padding:0 20px;display:flex;align-items:center;">
            @foreach(['Dashboard','Clients','Pipeline','Invoices','Bookings'] as $ti => $tab)
            <div style="padding:11px 14px;font-size:.76rem;font-weight:{{ $ti===2?'700':'500' }};color:{{ $ti===2?'#c4b5fd':'rgba(255,255,255,.22)' }};border-bottom:{{ $ti===2?'2px solid #7c3aed':'2px solid transparent' }};white-space:nowrap;cursor:default;">{{ $tab }}</div>
            @endforeach
        </div>

        {{-- Content --}}
        <div style="background:#0d0a1e;padding:26px 28px;min-height:400px;">

            <div class="flex items-center justify-between" style="margin-bottom:22px;">
                <div>
                    <p class="text-white font-bold" style="font-size:.9rem;letter-spacing:-.01em;">Deal Pipeline</p>
                    <p style="font-size:.7rem;color:rgba(255,255,255,.28);margin-top:4px;">7 active deals · £65,900 total value</p>
                </div>
                <button class="text-white font-bold rounded-xl" style="font-size:.72rem;padding:7px 15px;background:linear-gradient(135deg,#7c3aed,#4f46e5);">+ New Deal</button>
            </div>

            @php
            $crmPipeline = [
                ['Lead',     '#60a5fa', [['Acme Corp','£12,000','3d'],['Creative Co','£5,400','7d']]],
                ['Proposal', '#a78bfa', [['Blue Agency','£8,500','12d'],['SaaS Inc','£15k','8d']]],
                ['Closing',  '#fb923c', [['Nexus Ltd','£24,000','5d']]],
                ['Won',      '#34d399', [['TechCo','£6,800',null],['Agency X','£9,200',null]]],
            ];
            @endphp
            <div class="grid grid-cols-4 gap-3">
                @foreach($crmPipeline as [$col,$c,$deals])
                <div>
                    <div class="flex items-center justify-between" style="margin-bottom:10px;">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $c }};"></span>
                            <span style="font-size:.72rem;font-weight:700;color:{{ $c }};">{{ $col }}</span>
                        </div>
                        <span style="font-size:.65rem;color:rgba(255,255,255,.18);font-weight:600;">{{ count($deals) }}</span>
                    </div>
                    @foreach($deals as [$name,$val,$days])
                    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:13px 14px;margin-bottom:8px;">
                        <p style="font-size:.75rem;font-weight:600;color:rgba(255,255,255,.72);margin-bottom:6px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $name }}</p>
                        <p style="font-size:.9rem;font-weight:800;color:{{ $c }};margin-bottom:5px;letter-spacing:-.01em;">{{ $val }}</p>
                        @if($days)
                        <p style="font-size:.65rem;color:rgba(255,255,255,.22);">{{ $days }} in stage</p>
                        @else
                        <p style="font-size:.65rem;color:#34d399;font-weight:700;">✓ Closed</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>

        </div>
    </div>
    </div>

    {{-- Text column --}}
    <div class="reveal d1" style="padding:2.5rem 0 2.5rem 2rem;">
        <div class="sec-label" style="margin-bottom:1.5rem;">CRM & Pipeline</div>
        <h2 class="font-black leading-tight" style="font-size:clamp(2rem,3vw,2.8rem);letter-spacing:-.03em;color:#0a0714;margin-bottom:1.75rem;">
            Know exactly where<br>every deal stands.
        </h2>
        <p class="text-gray-600" style="font-size:1.05rem;line-height:1.95;margin-bottom:2.5rem;">
            Drag deals across your pipeline, log notes against contacts, and let WhizziQ alert you when something's going cold. You focus on the relationship we handle the admin.
        </p>
        <ul>
            @foreach([
                'Visual kanban pipeline drag, drop, and close deals',
                'Auto follow-up alerts when deals have been quiet 14+ days',
                'Every note, invoice, and booking linked to each contact',
            ] as $pt)
            <li class="check-li">
                <span class="check-icon">
                    <svg class="w-3 h-3" style="color:#7c3aed;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="text-gray-700 font-medium" style="font-size:.95rem;line-height:1.75;">{{ $pt }}</span>
            </li>
            @endforeach
        </ul>
    </div>

</div>
</div>
</section>


{{-- ══════════════════════════════════════════════════════════
     6 · FEATURE: INVOICING
     ══════════════════════════════════════════════════════════ --}}
<section class="bg-white" style="padding:136px 0;">
<div class="max-w-6xl mx-auto px-6">
<div class="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-24 items-center">

    {{-- Text column --}}
    <div class="reveal order-2 lg:order-1" style="padding:2.5rem 0;">
        <div class="sec-label" style="margin-bottom:1.5rem;">Invoicing & Payments</div>
        <h2 class="font-black leading-tight" style="font-size:clamp(2rem,3vw,2.8rem);letter-spacing:-.03em;color:#0a0714;margin-bottom:1.75rem;">
            Send an invoice<br>in 30 seconds.
        </h2>
        <p class="text-gray-600" style="font-size:1.05rem;line-height:1.95;margin-bottom:2.5rem;">
            Pick a client, add your line items, hit send. A Stripe payment link is attached automatically so clients can pay by card immediately. Overdue? Reminders go out on their own.
        </p>
        <ul>
            @foreach([
                'Stripe-powered card payments on every invoice',
                'Automatic overdue reminders no chasing needed',
                'Invoice status updates your client record instantly',
            ] as $pt)
            <li class="check-li">
                <span class="check-icon">
                    <svg class="w-3 h-3" style="color:#7c3aed;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="text-gray-700 font-medium" style="font-size:.95rem;line-height:1.75;">{{ $pt }}</span>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- Mock window --}}
    <div class="reveal d1 order-1 lg:order-2">
    <div class="app-win">
        <div class="win-bar" style="padding:14px 22px;">
            <div style="display:flex;gap:7px;">
                <div class="win-dot" style="background:#ff5f57;"></div>
                <div class="win-dot" style="background:#febc2e;"></div>
                <div class="win-dot" style="background:#28c840;"></div>
            </div>
            <div class="win-url" style="font-size:11px;padding:4px 18px;">app.whiziq.com/invoices</div>
        </div>

        <div class="flex" style="min-height:440px;">

            {{-- Sidebar --}}
            <div class="hidden md:flex flex-col gap-0.5 flex-shrink-0"
                 style="width:175px;border-right:1px solid rgba(255,255,255,.06);background:#100e22;padding:18px 10px;">
                <div class="flex items-center gap-2.5 px-3 py-2.5" style="margin-bottom:16px;">
                    <div class="w-6 h-6 rounded-lg flex-shrink-0" style="background:linear-gradient(135deg,#7c3aed,#4f46e5);"></div>
                    <span class="font-black text-white" style="font-size:.8rem;">WhizziQ</span>
                </div>
                @php
                $invNav = [
                    ['Dashboard','M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',false],
                    ['Clients','M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',false],
                    ['Invoices','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',true],
                    ['Bookings','M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',false],
                    ['Tasks','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',false],
                ];
                @endphp
                @foreach($invNav as [$lbl,$pth,$on])
                <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl"
                     style="{{ $on?'background:rgba(124,58,237,.22);':'' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         style="color:{{ $on?'#a78bfa':'rgba(255,255,255,.22)' }};">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $on?'2.2':'1.8' }}" d="{{ $pth }}"/>
                    </svg>
                    <span style="font-size:.78rem;font-weight:{{ $on?'700':'500' }};color:{{ $on?'#c4b5fd':'rgba(255,255,255,.25)' }};">{{ $lbl }}</span>
                </div>
                @endforeach
            </div>

            {{-- Main --}}
            <div class="flex-1" style="background:#0d0a1e;padding:24px 26px;">

                <div class="flex items-center justify-between" style="margin-bottom:20px;">
                    <div>
                        <p class="text-white font-bold" style="font-size:.9rem;">Invoices</p>
                        <p style="font-size:.7rem;color:rgba(255,255,255,.28);margin-top:4px;">£9,840 received this month</p>
                    </div>
                    <button class="text-white font-bold rounded-xl" style="font-size:.72rem;padding:7px 14px;background:linear-gradient(135deg,#7c3aed,#4f46e5);">+ New Invoice</button>
                </div>

                <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:20px 22px;">
                    <div class="flex items-start justify-between" style="margin-bottom:18px;">
                        <div>
                            <p style="font-size:.62rem;font-weight:700;color:rgba(255,255,255,.2);letter-spacing:.1em;margin-bottom:5px;">INVOICE</p>
                            <p class="text-white font-black" style="font-size:1.35rem;letter-spacing:-.03em;line-height:1;">INV-042</p>
                            <p style="font-size:.76rem;color:rgba(255,255,255,.45);margin-top:5px;">Acme Design Co. · Due 14 Apr 2026</p>
                        </div>
                        <span style="font-size:.7rem;font-weight:700;padding:5px 13px;border-radius:999px;background:rgba(52,211,153,.12);color:#34d399;border:1px solid rgba(52,211,153,.22);">PAID</span>
                    </div>

                    <div style="border:1px solid rgba(255,255,255,.07);border-radius:10px;overflow:hidden;margin-bottom:16px;">
                        @foreach([['Website Redesign','£2,500'],['Monthly Retainer','£800'],['SEO Audit','£500']] as [$itm,$prc])
                        <div class="flex items-center justify-between" style="padding:11px 16px;border-bottom:1px solid rgba(255,255,255,.05);background:rgba(255,255,255,.02);">
                            <p style="font-size:.8rem;font-weight:600;color:rgba(255,255,255,.68);">{{ $itm }}</p>
                            <p style="font-size:.84rem;font-weight:700;color:rgba(255,255,255,.88);">{{ $prc }}</p>
                        </div>
                        @endforeach
                        <div class="flex items-center justify-between" style="padding:13px 16px;background:rgba(124,58,237,.1);">
                            <p style="font-size:.82rem;font-weight:700;color:rgba(255,255,255,.5);">Total</p>
                            <p class="text-white font-black" style="font-size:1.15rem;letter-spacing:-.02em;">£3,800</p>
                        </div>
                    </div>

                    <div class="flex gap-2.5">
                        <button class="flex-1 font-bold text-white rounded-xl" style="font-size:.74rem;padding:10px;background:linear-gradient(135deg,#7c3aed,#4f46e5);">Pay with Card</button>
                        <button class="font-semibold rounded-xl border" style="font-size:.74rem;padding:10px 16px;color:rgba(255,255,255,.35);border-color:rgba(255,255,255,.08);">Download</button>
                        <button class="font-semibold rounded-xl border" style="font-size:.74rem;padding:10px 16px;color:rgba(255,255,255,.35);border-color:rgba(255,255,255,.08);">Share</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>

</div>
</div>
</section>


{{-- ══════════════════════════════════════════════════════════
     7 · FEATURE: AI DIGEST
     ══════════════════════════════════════════════════════════ --}}
<section style="background:#f9f7ff;padding:136px 0;">
<div class="max-w-6xl mx-auto px-6">
<div class="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-24 items-center">

    {{-- Mock window --}}
    <div class="reveal">
    <div class="app-win">
        <div class="win-bar" style="padding:14px 22px;">
            <div style="display:flex;gap:7px;">
                <div class="win-dot" style="background:#ff5f57;"></div>
                <div class="win-dot" style="background:#febc2e;"></div>
                <div class="win-dot" style="background:#28c840;"></div>
            </div>
            <div class="win-url" style="font-size:11px;padding:4px 18px;">app.whiziq.com/dashboard</div>
        </div>

        <div class="flex" style="min-height:440px;">

            {{-- Sidebar --}}
            <div class="hidden md:flex flex-col gap-0.5 flex-shrink-0"
                 style="width:175px;border-right:1px solid rgba(255,255,255,.06);background:#100e22;padding:18px 10px;">
                <div class="flex items-center gap-2.5 px-3 py-2.5" style="margin-bottom:16px;">
                    <div class="w-6 h-6 rounded-lg flex-shrink-0" style="background:linear-gradient(135deg,#7c3aed,#4f46e5);"></div>
                    <span class="font-black text-white" style="font-size:.8rem;">WhizziQ</span>
                </div>
                @php
                $aiNav = [
                    ['Dashboard','M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',true],
                    ['Clients','M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',false],
                    ['Invoices','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',false],
                    ['Bookings','M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',false],
                    ['Tasks','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',false],
                ];
                @endphp
                @foreach($aiNav as [$lbl,$pth,$on])
                <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl"
                     style="{{ $on?'background:rgba(124,58,237,.22);':'' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         style="color:{{ $on?'#a78bfa':'rgba(255,255,255,.22)' }};">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $on?'2.2':'1.8' }}" d="{{ $pth }}"/>
                    </svg>
                    <span style="font-size:.78rem;font-weight:{{ $on?'700':'500' }};color:{{ $on?'#c4b5fd':'rgba(255,255,255,.25)' }};">{{ $lbl }}</span>
                </div>
                @endforeach
            </div>

            {{-- Main --}}
            <div class="flex-1" style="background:#0d0a1e;padding:24px 26px;">

                <div class="flex items-center justify-between" style="margin-bottom:20px;">
                    <div class="flex items-center gap-2.5">
                        <span class="pulse-dot w-2 h-2 rounded-full flex-shrink-0" style="background:#a78bfa;"></span>
                        <div>
                            <p class="text-white font-bold" style="font-size:.9rem;">AI Morning Digest</p>
                            <p style="font-size:.7rem;color:rgba(255,255,255,.28);margin-top:3px;">Mon 28 Apr · 8:00 AM · 4 items</p>
                        </div>
                    </div>
                    <span style="font-size:.68rem;font-weight:700;padding:4px 11px;border-radius:999px;background:rgba(167,139,250,.12);color:#a78bfa;border:1px solid rgba(167,139,250,.22);">Live</span>
                </div>

                @foreach([
                    ['Invoice Overdue',   'Acme Corp £2,250 · 4 days past due',         'Reminder sent','rgba(251,146,60,.08)','rgba(251,146,60,.2)','#fb923c'],
                    ['Deal Going Cold',   'Brand Package £8,000 · 14 days, no contact', 'Follow up',    'rgba(167,139,250,.08)','rgba(167,139,250,.2)','#a78bfa'],
                    ['Revenue Up 18%',    'On track to hit your £10,000 April goal',       'On track',     'rgba(52,211,153,.08)','rgba(52,211,153,.2)','#34d399'],
                    ['Appointment Today', 'Strategy Call w/ TechCo at 10:00 AM',          'Confirmed',    'rgba(96,165,250,.08)','rgba(96,165,250,.2)','#60a5fa'],
                ] as [$tag,$body,$action,$bg,$bd,$c])
                <div class="flex items-start justify-between gap-3"
                     style="background:{{ $bg }};border:1px solid {{ $bd }};border-radius:12px;padding:14px 16px;margin-bottom:9px;">
                    <div class="flex-1 min-w-0">
                        <p style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:{{ $c }};margin-bottom:5px;">{{ $tag }}</p>
                        <p style="font-size:.8rem;color:rgba(255,255,255,.75);line-height:1.5;">{{ $body }}</p>
                    </div>
                    <span class="flex-shrink-0 font-bold whitespace-nowrap"
                          style="font-size:.68rem;padding:4px 10px;border-radius:8px;border:1px solid {{ $bd }};color:{{ $c }};">{{ $action }}</span>
                </div>
                @endforeach

            </div>
        </div>
    </div>
    </div>

    {{-- Text column --}}
    <div class="reveal d1" style="padding:2.5rem 0 2.5rem 2rem;">
        <div class="sec-label" style="margin-bottom:1.5rem;">AI Intelligence</div>
        <h2 class="font-black leading-tight" style="font-size:clamp(2rem,3vw,2.8rem);letter-spacing:-.03em;color:#0a0714;margin-bottom:1.75rem;">
            Wake up knowing<br>exactly what to do.
        </h2>
        <p class="text-gray-600" style="font-size:1.05rem;line-height:1.95;margin-bottom:2.5rem;">
            Every morning at 8 AM, WhizziQ's AI reviews your entire business overnight and surfaces what matters overdue invoices flagged, cold deals surfaced, goals tracked. No digging. Just clarity.
        </p>
        <ul>
            @foreach([
                'Morning digest lands in your inbox every day at 8 AM',
                'Flags overdue invoices, stale deals, and missed follow-ups',
                'Revenue trends and monthly goal tracking built right in',
            ] as $pt)
            <li class="check-li">
                <span class="check-icon">
                    <svg class="w-3 h-3" style="color:#7c3aed;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="text-gray-700 font-medium" style="font-size:.95rem;line-height:1.75;">{{ $pt }}</span>
            </li>
            @endforeach
        </ul>
    </div>

</div>
</div>
</section>


{{-- ══════════════════════════════════════════════════════════
     8 · MORE FEATURES GRID
     ══════════════════════════════════════════════════════════ --}}
<style>
@media (min-width:1024px) {
    .bento-grid { grid-template-columns: repeat(3,1fr); }
    .bento-wide { grid-column: span 2; }
}
.bento-card {
    position:relative;overflow:hidden;
    background:rgba(255,255,255,.025);
    border:1px solid rgba(255,255,255,.07);
    border-radius:1.25rem;
    padding:2.25rem 2.125rem 2rem;
    display:flex;flex-direction:column;
    transition:transform .28s ease,border-color .28s ease,box-shadow .28s ease;
}
.bento-card:hover {
    transform:translateY(-4px);
    border-color:rgba(255,255,255,.14);
    box-shadow:0 20px 48px rgba(0,0,0,.3);
}
</style>

<section style="background:#07050f;padding:128px 0;position:relative;overflow:hidden;">

    {{-- Background decoration --}}
    <div style="position:absolute;inset:0;background:linear-gradient(rgba(124,58,237,.04) 1px,transparent 1px) 0 0/52px 52px,linear-gradient(90deg,rgba(124,58,237,.04) 1px,transparent 1px) 0 0/52px 52px;pointer-events:none;"></div>
    <div style="position:absolute;top:-80px;left:50%;transform:translateX(-50%);width:800px;height:420px;background:radial-gradient(ellipse,rgba(124,58,237,.11) 0%,transparent 65%);pointer-events:none;"></div>

    <div class="max-w-5xl mx-auto px-6 relative">

        {{-- Header --}}
        <div class="text-center reveal" style="margin-bottom:5rem;">
            <span class="sec-label" style="color:#a78bfa;display:inline-flex;justify-content:center;margin-bottom:1.5rem;">And more</span>
            <h2 class="font-black text-white" style="font-size:clamp(2.4rem,5vw,3.6rem);letter-spacing:-.04em;line-height:1.08;">
                Everything else,<br><span class="grad-text">included.</span>
            </h2>
            <p class="mx-auto" style="color:rgba(255,255,255,.4);font-size:1.05rem;max-width:440px;line-height:1.85;margin-top:1.5rem;">
                No add-ons. No upsells. No feature gates. Every tool ships with every plan.
            </p>
        </div>

        {{-- Bento grid --}}
        @php
        $features = [
            ['#60a5fa','d1','01',true,
             'Online Booking',
             'Share your booking link and clients schedule themselves any time, any device. Confirmations, reminders, and follow-ups go out automatically. You show up to the call.'],
            ['#fb923c','d2','02',false,
             'Tasks & Boards',
             'Kanban boards wired directly to your clients and deals. Tasks trigger automatically when a deal changes stage.'],
            ['#f472b6','d3','03',false,
             'Expenses & Cash Flow',
             'Log expenses, see real profit, and know your cash position updated daily, not at month end.'],
            ['#34d399','d4','04',true,
             'Client Profitability',
             'A clear breakdown of revenue, collected, and outstanding per client so you always know which relationships are worth growing and which are costing you more than they return.'],
            ['#a78bfa','d5','05',false,
             'Smart Alerts',
             'Instant notifications for overdue invoices, stale deals, upcoming tasks, and financial anomalies before they become problems.'],
            ['#f87171','d6','06',true,
             'Aftercare & Follow-ups',
             'Automated WhatsApp and SMS messages sent to clients after appointments, project completions, or any custom trigger keep them coming back without lifting a finger.'],
        ];
        @endphp

        <div class="bento-grid grid grid-cols-1 gap-4">
            @foreach($features as [$c, $delay, $num, $wide, $title, $desc])
            <div class="reveal bento-card {{ $wide ? 'bento-wide' : '' }}">

                {{-- Animated glow --}}
                <div class="pain-glow {{ $delay }}"
                     style="position:absolute;top:-30px;right:-30px;width:160px;height:160px;border-radius:50%;background:{{ $c }};filter:blur(44px);pointer-events:none;opacity:.07;"></div>

                {{-- Large muted number --}}
                <div style="position:absolute;bottom:1rem;right:1.5rem;font-size:5.5rem;font-weight:900;line-height:1;letter-spacing:-.06em;color:{{ $c }};opacity:.05;pointer-events:none;user-select:none;">{{ $num }}</div>

                {{-- Top accent line --}}
                <div style="position:absolute;top:0;left:1.75rem;right:1.75rem;height:2px;border-radius:0 0 999px 999px;background:linear-gradient(90deg,{{ $c }},transparent);opacity:.5;"></div>

                {{-- Content --}}
                <div style="position:relative;">
                    <h3 class="font-bold text-white" style="font-size:{{ $wide ? '1.2rem' : '1.05rem' }};letter-spacing:-.02em;line-height:1.4;margin-bottom:1rem;">{{ $title }}</h3>
                    <p style="color:rgba(255,255,255,.38);font-size:.9rem;line-height:1.88;">{{ $desc }}</p>
                </div>

                {{-- Bottom accent --}}
                <div style="margin-top:auto;padding-top:1.75rem;">
                    <div style="height:2px;width:2rem;border-radius:999px;background:{{ $c }};opacity:.45;"></div>
                </div>

            </div>
            @endforeach
        </div>

    </div>
</section>


{{-- ══════════════════════════════════════════════════════════
     11 · PRICING
     ══════════════════════════════════════════════════════════ --}}
<section style="background:#07050f;padding:128px 0;position:relative;overflow:hidden;" id="pricing">

    {{-- Background --}}
    <div style="position:absolute;inset:0;background:linear-gradient(rgba(124,58,237,.04) 1px,transparent 1px) 0 0/52px 52px,linear-gradient(90deg,rgba(124,58,237,.04) 1px,transparent 1px) 0 0/52px 52px;pointer-events:none;"></div>
    <div style="position:absolute;top:-100px;left:50%;transform:translateX(-50%);width:900px;height:500px;background:radial-gradient(ellipse,rgba(124,58,237,.12) 0%,transparent 65%);pointer-events:none;"></div>

    <div class="max-w-6xl mx-auto px-6 relative">

        {{-- Header --}}
        <div class="text-center reveal" style="margin-bottom:4.5rem;">
            <span class="sec-label" style="color:#a78bfa;display:inline-flex;justify-content:center;margin-bottom:1.5rem;">Pricing</span>
            <h2 class="font-black text-white" style="font-size:clamp(2.4rem,5vw,3.8rem);letter-spacing:-.04em;line-height:1.08;">
                One plan for every stage.<br><span class="grad-text">Zero surprises.</span>
            </h2>
            <p class="mx-auto" style="color:rgba(255,255,255,.4);font-size:1.05rem;max-width:460px;line-height:1.85;margin-top:1.5rem;">
                14-day free trial on every plan. No credit card required. Cancel anytime.
            </p>
        </div>

        <div x-data="{ annual: false }" class="reveal">

            {{-- Billing toggle --}}
            <div style="display:flex;align-items:center;justify-content:center;gap:.75rem;margin-bottom:4rem;">

                <button
                    @click="annual=false"
                    :style="!annual
                        ? 'border-radius:9999px;padding:13px 28px;display:inline-flex;align-items:center;gap:9px;font-size:.9rem;font-family:inherit;cursor:pointer;transition:all .2s ease;line-height:1;white-space:nowrap;outline:none;background:rgba(139,92,246,0.25);color:#fff;border:1.5px solid rgba(139,92,246,0.8);font-weight:600;'
                        : 'border-radius:9999px;padding:13px 28px;display:inline-flex;align-items:center;gap:9px;font-size:.9rem;font-family:inherit;cursor:pointer;transition:all .2s ease;line-height:1;white-space:nowrap;outline:none;background:transparent;color:rgba(255,255,255,0.52);border:1.5px solid rgba(255,255,255,0.18);font-weight:400;'">
                    Monthly
                </button>

                <button
                    @click="annual=true"
                    :style="annual
                        ? 'border-radius:9999px;padding:13px 28px;display:inline-flex;align-items:center;gap:9px;font-size:.9rem;font-family:inherit;cursor:pointer;transition:all .2s ease;line-height:1;white-space:nowrap;outline:none;background:rgba(139,92,246,0.25);color:#fff;border:1.5px solid rgba(139,92,246,0.8);font-weight:600;'
                        : 'border-radius:9999px;padding:13px 28px;display:inline-flex;align-items:center;gap:9px;font-size:.9rem;font-family:inherit;cursor:pointer;transition:all .2s ease;line-height:1;white-space:nowrap;outline:none;background:transparent;color:rgba(255,255,255,0.52);border:1.5px solid rgba(255,255,255,0.18);font-weight:400;'">
                    Annual
                    <span style="font-size:.62rem;font-weight:800;letter-spacing:.07em;padding:3px 9px;border-radius:999px;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;white-space:nowrap;line-height:1;">SAVE 20%</span>
                </button>

            </div>

            {{-- Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 items-stretch" style="gap:1.25rem;">

                {{-- ── Starter ── --}}
                <div class="reveal rounded-2xl flex flex-col"
                     style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.09);">
                    <div class="flex-1 flex flex-col" style="padding:2.25rem 2rem;">

                        {{-- Plan header --}}
                        <div class="flex items-center gap-2.5" style="margin-bottom:2rem;">
                            <span style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.16em;color:rgba(255,255,255,.32);">Starter</span>
                            <span style="font-size:.65rem;font-weight:700;padding:3px 10px;border-radius:999px;background:rgba(96,165,250,.12);border:1px solid rgba(96,165,250,.25);color:#60a5fa;">Solo freelancers</span>
                        </div>

                        {{-- Price --}}
                        <div style="margin-bottom:1.75rem;">
                            <div class="flex items-end gap-1.5" style="line-height:1;">
                                <span x-text="annual ? '$24.00' : '$29.99'"
                                      class="text-white font-black"
                                      style="font-size:3.75rem;letter-spacing:-.05em;"></span>
                                <span style="font-size:.9rem;color:rgba(255,255,255,.38);font-weight:600;padding-bottom:.6rem;">/mo</span>
                            </div>
                            <div style="margin-top:.6rem;min-height:1.25rem;">
                                <p x-show="annual" style="font-size:.78rem;color:rgba(255,255,255,.28);">Billed $288/yr · you save $72</p>
                                <p x-show="!annual" style="font-size:.78rem;color:rgba(255,255,255,.22);">Switch to annual and save $72/yr</p>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div style="height:1px;background:rgba(255,255,255,.07);margin-bottom:1.75rem;"></div>

                        {{-- Features --}}
                        <div class="flex-1">
                            <p style="font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.2);margin-bottom:1.1rem;">What's included</p>
                            <ul style="display:flex;flex-direction:column;gap:1rem;margin-bottom:1.75rem;">
                                @foreach([
                                    ['500 contacts & lead pipeline', '#60a5fa'],
                                    ['Unlimited online bookings', '#60a5fa'],
                                    ['50 invoices per month', '#60a5fa'],
                                    ['Stripe card payments', '#60a5fa'],
                                    ['AI morning digest', '#60a5fa'],
                                    ['Email support', '#60a5fa'],
                                ] as [$feat, $cc])
                                <li class="flex items-start gap-3">
                                    <svg class="w-4 h-4 flex-shrink-0" style="color:{{ $cc }};margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span style="font-size:.875rem;color:rgba(255,255,255,.6);line-height:1.55;">{{ $feat }}</span>
                                </li>
                                @endforeach
                            </ul>
                            <div style="padding:12px 14px;border-radius:10px;background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);margin-bottom:1.75rem;">
                                <p style="font-size:.78rem;color:rgba(255,255,255,.2);line-height:1.65;">Automations, WhatsApp & SMS, and team tools are available on Pro.</p>
                            </div>
                        </div>

                        {{-- CTA --}}
                        <a href="{{ route('register') }}"
                           class="block text-center font-semibold rounded-2xl transition-all hover:bg-white/5"
                           style="padding:15px 24px;border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.65);font-size:.9rem;">
                            Start free 14 days
                        </a>
                    </div>
                </div>

                {{-- ── Growth (Featured) ── --}}
                <div class="reveal d1 rounded-2xl flex flex-col relative"
                     style="background:#0d0921;border:2px solid rgba(124,58,237,.5);box-shadow:0 0 0 1px rgba(124,58,237,.15),0 0 60px rgba(124,58,237,.2),0 0 120px rgba(124,58,237,.07),0 24px 56px rgba(0,0,0,.5);">

                    {{-- Most popular badge --}}
                    <div style="position:absolute;top:-15px;inset-x:0;display:flex;justify-content:center;">
                        <span class="text-white font-black uppercase"
                              style="font-size:.62rem;letter-spacing:.1em;padding:6px 20px;border-radius:999px;background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 4px 20px rgba(124,58,237,.6);">
                            Most Popular
                        </span>
                    </div>

                    <div class="flex-1 flex flex-col" style="padding:2.25rem 2rem;">

                        {{-- Plan header --}}
                        <div class="flex items-center gap-2.5" style="margin-bottom:2rem;">
                            <span style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.16em;color:rgba(196,181,253,.5);">Pro</span>
                            <span style="font-size:.65rem;font-weight:700;padding:3px 10px;border-radius:999px;background:rgba(124,58,237,.2);border:1px solid rgba(124,58,237,.4);color:#c4b5fd;">Growing businesses</span>
                        </div>

                        {{-- Price --}}
                        <div style="margin-bottom:1.75rem;">
                            <div class="flex items-end gap-1.5" style="line-height:1;">
                                <span x-text="annual ? '$32.00' : '$39.99'"
                                      class="text-white font-black"
                                      style="font-size:3.75rem;letter-spacing:-.05em;"></span>
                                <span style="font-size:.9rem;color:rgba(196,181,253,.55);font-weight:600;padding-bottom:.6rem;">/mo</span>
                            </div>
                            <div style="margin-top:.6rem;min-height:1.25rem;">
                                <p x-show="annual" style="font-size:.78rem;color:rgba(196,181,253,.4);">Billed $384/yr · you save $96</p>
                                <p x-show="!annual" style="font-size:.78rem;color:rgba(196,181,253,.3);">Switch to annual and save $96/yr</p>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div style="height:1px;background:rgba(124,58,237,.25);margin-bottom:1.75rem;"></div>

                        {{-- Features --}}
                        <div class="flex-1">
                            <p style="font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:rgba(196,181,253,.3);margin-bottom:1.1rem;">Everything in Starter, plus</p>
                            <ul style="display:flex;flex-direction:column;gap:1rem;margin-bottom:1.75rem;">
                                @foreach([
                                    'Unlimited contacts & invoices',
                                    'Expense tracking & cash flow',
                                    'Client profitability dashboard',
                                    'Deal pipeline & CRM tools',
                                    'WhatsApp & SMS automations',
                                    'Aftercare & follow-up sequences',
                                    'Auto alerts for stale deals',
                                    'Priority email support',
                                ] as $feat)
                                <li class="flex items-start gap-3">
                                    <svg class="w-4 h-4 flex-shrink-0" style="color:#a78bfa;margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span style="font-size:.875rem;color:rgba(255,255,255,.78);line-height:1.55;">{{ $feat }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- CTA --}}
                        <a href="{{ route('register') }}"
                           class="block text-center font-bold text-white rounded-2xl transition-all hover:opacity-90"
                           style="padding:16px 24px;background:linear-gradient(135deg,#7c3aed,#4f46e5);box-shadow:0 8px 32px rgba(124,58,237,.55);font-size:.9rem;">
                            Start free 14 days
                        </a>
                    </div>
                </div>

                {{-- ── Agency ── --}}
                <div class="reveal d2 rounded-2xl flex flex-col"
                     style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.09);">
                    <div class="flex-1 flex flex-col" style="padding:2.25rem 2rem;">

                        {{-- Plan header --}}
                        <div class="flex items-center gap-2.5" style="margin-bottom:2rem;">
                            <span style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.16em;color:rgba(255,255,255,.32);">Premium</span>
                            <span style="font-size:.65rem;font-weight:700;padding:3px 10px;border-radius:999px;background:rgba(251,146,60,.1);border:1px solid rgba(251,146,60,.25);color:#fb923c;">Teams & agencies</span>
                        </div>

                        {{-- Price --}}
                        <div style="margin-bottom:1.75rem;">
                            <div class="flex items-end gap-1.5" style="line-height:1;">
                                <span x-text="annual ? '$40.00' : '$49.99'"
                                      class="text-white font-black"
                                      style="font-size:3.75rem;letter-spacing:-.05em;"></span>
                                <span style="font-size:.9rem;color:rgba(255,255,255,.38);font-weight:600;padding-bottom:.6rem;">/mo</span>
                            </div>
                            <div style="margin-top:.6rem;min-height:1.25rem;">
                                <p x-show="annual" style="font-size:.78rem;color:rgba(255,255,255,.28);">Billed $480/yr · you save $120</p>
                                <p x-show="!annual" style="font-size:.78rem;color:rgba(255,255,255,.22);">Switch to annual and save $120/yr</p>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div style="height:1px;background:rgba(255,255,255,.07);margin-bottom:1.75rem;"></div>

                        {{-- Features --}}
                        <div class="flex-1">
                            <p style="font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.2);margin-bottom:1.1rem;">Everything in Pro, plus</p>
                            <ul style="display:flex;flex-direction:column;gap:1rem;margin-bottom:1.75rem;">
                                @foreach([
                                    ['Team members up to 5 seats', '#fb923c'],
                                    ['Role-based access control', '#fb923c'],
                                    ['AI-powered tax management', '#fb923c'],
                                    ['Unlimited file storage', '#fb923c'],
                                    ['Custom onboarding call', '#fb923c'],
                                    ['Dedicated account support', '#fb923c'],
                                ] as [$feat, $cc])
                                <li class="flex items-start gap-3">
                                    <svg class="w-4 h-4 flex-shrink-0" style="color:{{ $cc }};margin-top:2px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span style="font-size:.875rem;color:rgba(255,255,255,.6);line-height:1.55;">{{ $feat }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- CTA --}}
                        <a href="{{ route('register') }}"
                           class="block text-center font-semibold rounded-2xl transition-all hover:bg-white/5"
                           style="padding:15px 24px;border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.65);font-size:.9rem;">
                            Start free 14 days
                        </a>
                    </div>
                </div>

            </div>

            {{-- Trust row --}}
            <div class="flex flex-wrap items-center justify-center gap-6" style="margin-top:3rem;">
                @foreach(['No credit card required','Cancel anytime','Switch plans freely'] as $t)
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:#a78bfa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span style="font-size:.82rem;color:rgba(255,255,255,.28);">{{ $t }}</span>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</section>


{{-- ══════════════════════════════════════════════════════════
     12 · FAQ
     ══════════════════════════════════════════════════════════ --}}
<section id="faq" style="background:#07050f;padding:112px 0 120px;position:relative;overflow:hidden;">

    {{-- Background texture --}}
    <div style="position:absolute;inset:0;pointer-events:none;
        background:
            radial-gradient(ellipse 60% 50% at 50% 0%,rgba(124,58,237,.08) 0%,transparent 70%),
            linear-gradient(rgba(124,58,237,.04) 1px,transparent 1px) 0 0/52px 52px,
            linear-gradient(90deg,rgba(124,58,237,.04) 1px,transparent 1px) 0 0/52px 52px;">
    </div>

<div class="max-w-3xl mx-auto px-6" style="position:relative;z-index:1;">

    {{-- Header --}}
    <div class="text-center reveal" style="margin-bottom:3.5rem;">
        <div class="sec-label" style="display:inline-flex;justify-content:center;color:#a78bfa;">Questions</div>
        <h2 class="font-black text-white leading-tight" style="font-size:clamp(2.2rem,4vw,3rem);letter-spacing:-.03em;margin-bottom:.75rem;">
            Everything you need to know
        </h2>
        <p style="color:rgba(255,255,255,.42);font-size:1.05rem;line-height:1.75;">
            Still curious? Email us and we'll respond within one business day.
        </p>
    </div>

    @php
    $faqs = [
        ['No credit card really?',
         'Yes. Create your account and explore every feature for 14 days. We only ask for payment details when you decide to stay not before. If you cancel during the trial, you pay nothing.'],
        ['Can I import my existing clients?',
         'Upload a CSV and WhizziQ maps your columns automatically. Contacts, email addresses, phone numbers, tags everything comes across in under 2 minutes. No manual data entry.'],
        ['What tools does WhizziQ replace?',
         'Most users cancel 4–6 subscriptions within their first month. WhizziQ combines your CRM, invoicing, client booking, task management, and expense tracking all in one place, for far less.'],
        ['Do I need any technical knowledge?',
         'None at all. Your booking page, invoice templates, and client portal are ready in under 10 minutes. No code, no developer, no setup headaches.'],
        ['Is my data safe?',
         'Everything is encrypted at rest and in transit. WhizziQ is GDPR-compliant, never sells your data, and supports two-factor authentication on all plans. You own your data, always.'],
        ['Can I change or cancel my plan at any time?',
         'Yes. Upgrade, downgrade, or cancel from your account settings at any time. No lock-in contracts, no cancellation fees, no awkward phone calls. One click and you\'re done.'],
        ['Does WhizziQ support multiple currencies?',
         'Yes. Set your preferred currency in Booking Settings and it applies across all invoices, client receipts, and financial dashboards automatically.'],
    ];
    @endphp

    <div class="reveal" x-data="{open: 1}"
         style="border:1px solid rgba(255,255,255,.07);border-radius:1.5rem;overflow:hidden;
                background:rgba(255,255,255,.02);backdrop-filter:blur(8px);
                box-shadow:0 0 0 1px rgba(124,58,237,.08),0 32px 80px rgba(0,0,0,.4);">

        @foreach($faqs as $idx => [$q, $a])
        <div class="faq-item" @if($loop->last) style="border-bottom:none;" @endif>
            <button class="faq-q" @click="open === {{ $idx+1 }} ? open=0 : open={{ $idx+1 }}">
                {{-- Number badge --}}
                <span class="faq-num">{{ str_pad($idx+1, 2, '0', STR_PAD_LEFT) }}</span>

                {{-- Question text --}}
                <span class="flex-1 text-left font-semibold"
                      style="font-size:.975rem;line-height:1.55;"
                      :style="open === {{ $idx+1 }} ? 'color:#e9d5ff' : 'color:rgba(255,255,255,.78)'">
                    {{ $q }}
                </span>

                {{-- Chevron icon in circle --}}
                <span class="faq-icon"
                      :style="open === {{ $idx+1 }} ? 'background:rgba(124,58,237,.28);border-color:rgba(124,58,237,.5)' : ''">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         :style="open === {{ $idx+1 }} ? 'transform:rotate(180deg);color:#c4b5fd' : 'color:rgba(167,139,250,.5)'"
                         style="transition:transform .28s cubic-bezier(.16,1,.3,1),color .2s ease;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </span>
            </button>

            <div x-show="open === {{ $idx+1 }}"
                 x-transition:enter="transition ease-out duration-220"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-end="opacity-0"
                 style="padding:0 2rem 1.75rem 4.25rem;">
                <p style="color:rgba(255,255,255,.48);font-size:.9375rem;line-height:1.9;">{{ $a }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Bottom help nudge --}}
    <div class="text-center reveal" style="margin-top:2.5rem;">
        <p style="font-size:.875rem;color:rgba(255,255,255,.28);">
            Still have questions?&nbsp;
            <a href="mailto:{{ config('mail.from.address', 'hello@whiziq.com') }}"
               style="color:#a78bfa;font-weight:600;text-decoration:none;"
               onmouseover="this.style.textDecoration='underline'"
               onmouseout="this.style.textDecoration='none'">Email us →</a>
        </p>
    </div>

</div>
</section>


{{-- ══════════════════════════════════════════════════════════
     13 · FINAL CTA
     ══════════════════════════════════════════════════════════ --}}
<section class="cta-bg" style="padding:120px 0 140px;">
<div class="max-w-4xl mx-auto px-6 text-center">

    {{-- Badge --}}
    <div class="reveal inline-flex items-center gap-2 mb-8 px-4 py-2 rounded-full"
         style="background:rgba(124,58,237,.12);border:1px solid rgba(124,58,237,.28);">
        <span class="pulse-dot w-1.5 h-1.5 rounded-full" style="background:#a78bfa;"></span>
        <span class="text-xs font-bold" style="color:#c4b5fd;letter-spacing:.06em;">14-day free trial · No credit card required</span>
    </div>

    {{-- Headline --}}
    <h2 class="reveal d1 font-black text-white leading-[1.04] tracking-[-0.04em]"
        style="font-size:clamp(2.8rem,6vw,4.6rem);margin-bottom:1.5rem;">
        Stop juggling apps.<br>
        <span class="grad-text-warm">Start growing.</span>
    </h2>

    {{-- Sub --}}
    <p class="reveal d2" style="color:rgba(255,255,255,.52);font-size:1.2rem;line-height:1.8;max-width:520px;margin:0 auto 3.5rem;">
        Join solo operators and small teams who run their entire business from one place CRM, invoices, bookings, tasks, and AI.
    </p>

    {{-- CTAs --}}
    <div class="reveal d3 flex flex-wrap items-center justify-center gap-4" style="margin-bottom:2.5rem;">
        <a href="{{ route('register') }}"
           class="inline-flex items-center gap-2.5 text-white font-bold text-base rounded-2xl whitespace-nowrap transition-all hover:-translate-y-0.5 active:translate-y-0"
           style="padding:19px 48px;font-size:1.05rem;background:linear-gradient(135deg,#7c3aed 0%,#4f46e5 100%);box-shadow:0 0 0 1px rgba(124,58,237,.55),0 16px 56px rgba(124,58,237,.55);">
            Start for free
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
        <a href="#pricing"
           class="inline-flex items-center gap-2 font-semibold text-base whitespace-nowrap rounded-2xl border transition-all"
           style="padding:19px 32px;color:rgba(255,255,255,.55);border-color:rgba(255,255,255,.14);"
           onmouseover="this.style.background='rgba(255,255,255,.05)'"
           onmouseout="this.style.background='transparent'">
            View pricing
        </a>
    </div>

    {{-- Trust signals --}}
    <div class="reveal d4 flex flex-wrap items-center justify-center gap-x-8 gap-y-3">
        @foreach([
            ['M5 13l4 4L19 7', 'No setup fees'],
            ['M5 13l4 4L19 7', 'Cancel anytime'],
            ['M5 13l4 4L19 7', 'Switch plans freely'],
            ['M5 13l4 4L19 7', 'Data export included'],
        ] as [$path, $label])
        <span class="flex items-center gap-2" style="font-size:.82rem;color:rgba(255,255,255,.32);font-weight:500;">
            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:rgba(167,139,250,.55);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $path }}"/>
            </svg>
            {{ $label }}
        </span>
        @endforeach
    </div>

</div>
</section>


<script>
(function () {
    const io = new IntersectionObserver(
        entries => entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('in');
                io.unobserve(e.target);
            }
        }),
        { threshold: 0.06 }
    );
    document.querySelectorAll('.reveal').forEach(el => io.observe(el));
})();
</script>

</x-layouts.app>
