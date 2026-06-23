<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Adoption Advisory') Workbench</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<style>
:root{
  --emerald:#059669; --emerald-dark:#047857; --emerald-deep:#064e3b; --emerald-light:#ecfdf5;
  --gold:#C9A227; --gold-soft:#F3E7BE; --gold-deep:#9c7c14;
  --banner-bg:#083c2d; --body-bg:#f4f7f5; --border:#dfe7e2; --muted:#6b7c74; --text:#13241d;
  --green:#059669; --amber:#d4a017; --red:#dc2626;
  --navbox:#042f22; --navbox-hover:#063f2e; --navbox-border:#0b5a40;
  --sidebar-text:#a7d8c4; --bh:54px; --sw:226px;
}
*,*::before,*::after{box-sizing:border-box;}
body{background:var(--body-bg);font-family:'Segoe UI',system-ui,sans-serif;margin:0;color:var(--text);}
.top-banner{background:var(--banner-bg);color:#fff;height:var(--bh);padding:0 22px;display:flex;align-items:center;
  justify-content:space-between;position:fixed;top:0;left:0;right:0;z-index:200;border-bottom:2px solid var(--gold);}
.brand{font-weight:700;font-size:.97rem;}.brand em{color:var(--gold);font-style:normal;}
.co-tag{font-size:.72rem;color:#a7d8c4;margin-top:2px;}
.clock{font-size:.75rem;color:#a7d8c4;text-align:right;line-height:1.5;}
.sidebar{background:transparent;width:var(--sw);position:fixed;top:var(--bh);bottom:38px;left:0;overflow-y:auto;
  padding:14px 10px;z-index:100;display:flex;flex-direction:column;gap:11px;}
.nav-group{background:var(--navbox);border:1px solid var(--navbox-border);border-radius:13px;
  padding:7px 6px 9px;box-shadow:0 8px 22px rgba(4,30,20,.28);}
.s-label{padding:7px 12px 5px;font-size:.66rem;text-transform:uppercase;letter-spacing:.09em;color:#5a9a7e;font-weight:700;}
.s-link{display:flex;align-items:center;gap:9px;padding:8px 12px;font-size:.84rem;color:var(--sidebar-text);
  text-decoration:none;border-left:3px solid transparent;border-radius:8px;margin:0 2px;transition:all .12s;}
.s-link:hover{background:var(--navbox-hover);color:#fff;}
.s-link.active{background:var(--navbox-hover);color:#fff;border-left-color:var(--gold);}
.s-link .pill{margin-left:auto;background:var(--gold);color:#053b2c;font-size:.62rem;font-weight:700;border-radius:10px;padding:0 6px;}
.main-wrap{margin-left:var(--sw);margin-top:var(--bh);padding:26px 30px 52px;min-height:calc(100vh - var(--bh));}
.ph{margin-bottom:22px;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;}
.ph h2{font-size:1.4rem;font-weight:700;margin:0;color:var(--emerald-deep);}
.ph p{color:var(--muted);margin:3px 0 0;font-size:.85rem;}
.btn-primary{background:var(--emerald);border-color:var(--emerald);}
.btn-primary:hover{background:var(--emerald-dark);border-color:var(--emerald-dark);}
.btn-outline-primary{color:var(--emerald-dark);border-color:var(--emerald);}
.btn-outline-primary:hover{background:var(--emerald);border-color:var(--emerald);}
.btn-gold{background:var(--gold);border-color:var(--gold);color:#053b2c;font-weight:600;}
.btn-gold:hover{background:var(--gold-deep);border-color:var(--gold-deep);color:#fff;}
.card-p{background:#fff;border:1px solid var(--border);border-radius:10px;}
.ch{padding:12px 16px;border-bottom:1px solid var(--border);font-weight:600;font-size:.88rem;
  display:flex;align-items:center;justify-content:space-between;color:var(--emerald-deep);}
.cb{padding:16px;}
.kpi{background:#fff;border:1px solid var(--border);border-radius:10px;padding:18px 16px;position:relative;overflow:hidden;}
.kpi::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--emerald);}
.kpi.gold::before{background:var(--gold);}
.kv{font-size:1.9rem;font-weight:700;line-height:1;color:var(--emerald-deep);}
.kl{font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);margin-top:5px;}
.ks{font-size:.76rem;color:var(--muted);margin-top:3px;}
.badge-st{display:inline-block;padding:2px 8px;border-radius:4px;font-size:.73rem;font-weight:600;}
.st-active,.st-responded,.st-sent,.st-current-user{background:#d1fae5;color:#065f46;}
.st-opened,.st-planner{background:#fef3c7;color:#92400e;}
.st-draft,.st-non-adopter,.st-closed{background:#f1f5f9;color:#475569;}
.tl{display:inline-flex;align-items:center;gap:5px;font-size:.74rem;font-weight:600;}
.tl-dot{width:9px;height:9px;border-radius:50%;display:inline-block;}
.tl-ahead .tl-dot{background:var(--green);}.tl-ahead{color:var(--green);}
.tl-at .tl-dot{background:var(--amber);}.tl-at{color:var(--amber);}
.tl-behind .tl-dot{background:var(--red);}.tl-behind{color:var(--red);}
.score-chip{display:inline-flex;align-items:center;justify-content:center;min-width:38px;padding:2px 8px;
  border-radius:20px;font-weight:700;font-size:.8rem;color:#fff;}
.table{font-size:.83rem;}.table th{font-size:.71rem;text-transform:uppercase;letter-spacing:.04em;
  color:var(--muted);font-weight:600;background:#eef4f0;}
.table-hover tbody tr:hover{background:var(--emerald-light);}
.form-label-sm{font-size:.78rem;font-weight:600;margin-bottom:3px;}
.form-control:focus,.form-select:focus{border-color:var(--emerald);box-shadow:0 0 0 .2rem rgba(5,150,105,.18);}
.svc{background:#fff;border:1px solid var(--border);border-radius:10px;padding:18px;height:100%;border-top:3px solid var(--gold);transition:all .15s;}
.svc:hover{box-shadow:0 6px 18px rgba(6,78,59,.1);transform:translateY(-2px);}
.svc h5{font-size:1rem;font-weight:700;color:var(--emerald-deep);margin-bottom:4px;}
.svc .price{font-size:1.3rem;font-weight:700;color:var(--gold-deep);}
.stat-pill{display:inline-flex;align-items:center;gap:6px;padding:4px 11px;background:#fff;border:1px solid var(--border);border-radius:20px;font-size:.76rem;}
.stat-pill b{color:var(--emerald-deep);}
.funnel-step{display:flex;align-items:center;gap:14px;margin-bottom:8px;}
.funnel-bar{height:34px;border-radius:6px;display:flex;align-items:center;padding:0 12px;color:#fff;font-weight:600;font-size:.84rem;min-width:60px;}
.funnel-meta{font-size:.78rem;color:var(--muted);white-space:nowrap;}
.prog-sm{height:6px;border-radius:3px;background:#e2e8f0;overflow:hidden;}.prog-sm div{height:6px;border-radius:3px;background:var(--emerald);}
.run-banner{position:fixed;left:0;right:0;bottom:0;height:36px;background:var(--banner-bg);border-top:2px solid var(--gold);
  display:flex;align-items:center;justify-content:center;overflow:hidden;z-index:250;}
.run-banner .track{white-space:nowrap;color:#fff;font-size:.94rem;font-weight:500;letter-spacing:.04em;}
.run-banner .track b{color:var(--gold);font-weight:700;}
#toast-area{position:fixed;bottom:46px;right:18px;z-index:999;display:flex;flex-direction:column;gap:7px;}
.t-msg{background:var(--emerald-deep);color:#fff;padding:9px 14px;border-radius:7px;font-size:.82rem;
  box-shadow:0 4px 12px rgba(0,0,0,.2);border-left:3px solid var(--gold);}
</style>
</head>
<body data-view="@yield('view')">

<div class="top-banner">
  <div>
    <div class="brand">Adoption <em>Advisory</em> Workbench</div>
    <div class="co-tag">AI &amp; Digital Adoption Consultancy · Client Operations Hub · Laravel</div>
  </div>
  <div class="d-flex align-items-center gap-3">
    <span class="badge" style="background:var(--gold);color:#053b2c;font-size:.65rem;">Live</span>
    <span class="badge bg-light text-dark" style="font-size:.65rem;">Local Only</span>
    <div class="clock" id="clock"></div>
  </div>
</div>

@php $on = fn ($name) => request()->routeIs($name) ? 'active' : ''; @endphp
<nav class="sidebar">
  <div class="nav-group">
    <div class="s-label">Overview</div>
    <a class="s-link {{ $on('workbench.dashboard') }}" href="{{ route('workbench.dashboard') }}">Dashboard</a>
  </div>
  <div class="nav-group">
    <div class="s-label">Practice</div>
    <a class="s-link {{ $on('workbench.services') }}" href="{{ route('workbench.services') }}">Services Management</a>
    <a class="s-link {{ $on('workbench.customers') }}" href="{{ route('workbench.customers') }}">Customer Database</a>
  </div>
  <div class="nav-group">
    <div class="s-label">Outreach</div>
    <a class="s-link {{ $on('workbench.emails') }}" href="{{ route('workbench.emails') }}">Email Management</a>
    <a class="s-link {{ $on('workbench.tracking') }}" href="{{ route('workbench.tracking') }}">Response Tracking</a>
  </div>
  <div class="nav-group">
    <div class="s-label">Insights</div>
    <a class="s-link {{ $on('workbench.insights') }}" href="{{ route('workbench.insights') }}">Adoption Insights</a>
    <a class="s-link {{ $on('workbench.support') }}" href="{{ route('workbench.support') }}">Govt Support &amp; Direction</a>
  </div>
</nav>

<div class="main-wrap">
  @yield('content')
</div>

<div id="toast-area"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const REF = {
  sectors:  @json($sectors),
  bench:    @json($bench),
  sizes:    @json($sizes),
  states:   @json($states),
  techs:    @json($techs),
  barriers: @json($barriers),
  phases:   @json($phases),
};
(function clock(){ var el=document.getElementById('clock'); if(!el) return;
  function t(){ var n=new Date(); el.innerHTML=n.toLocaleDateString('en-GB',{weekday:'short',day:'numeric',month:'short'})+'<br>'+n.toLocaleTimeString('en-GB'); }
  t(); setInterval(t,1000); })();
</script>
@include('partials.engine')
</body>
</html>
