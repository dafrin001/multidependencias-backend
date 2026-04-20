@extends('layouts.admin')

@section('title', 'Dashboard Global')
@section('page_title', 'Escritorio Principal')
@section('page_subtitle', 'Panel de control de dependencias municipales')

@section('styles')
<style>
    .dependency-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
        margin-top: 20px;
    }

    .dep-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 30px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        color: inherit;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .dep-card:hover {
        transform: translateY(-8px);
        background: rgba(30, 41, 59, 0.8);
        border-color: var(--primary);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3), 0 0 10px var(--primary-glow);
    }

    .dep-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        background: var(--glass);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        border: 1px solid var(--glass-border);
        transition: all 0.3s;
    }

    .dep-card:hover .dep-icon {
        background: var(--primary);
        color: white;
        transform: scale(1.1) rotate(5deg);
    }

    .dep-info h3 {
        font-size: 1.4rem;
        margin-bottom: 5px;
    }

    .dep-info p {
        color: var(--text-dim);
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .dep-footer {
        margin-top: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid var(--border);
    }

    .btn-access {
        padding: 8px 16px;
        background: var(--primary);
        color: white;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.3s;
    }

    .dep-card:hover .btn-access {
        background: white;
        color: var(--primary);
    }

    .status-badge {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        color: var(--secondary);
    }

    .dot {
        width: 8px;
        height: 8px;
        background: var(--secondary);
        border-radius: 50%;
        box-shadow: 0 0 10px var(--secondary);
    }

    .welcome-banner {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(16, 185, 129, 0.1));
        border: 1px solid var(--border);
        border-radius: 24px;
        padding: 40px;
        margin-bottom: 40px;
        display: flex;
        align-items: center;
        gap: 40px;
        position: relative;
        overflow: hidden;
    }

    .welcome-text h2 {
        font-size: 2.2rem;
        margin-bottom: 10px;
        background: linear-gradient(to right, #fff, #94a3b8);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    @media (max-width: 768px) {
        .welcome-banner {
            flex-direction: column;
            text-align: center;
            padding: 30px;
        }
    }
</style>
@section('content')
<div class="welcome-banner">
    <div class="welcome-text">
        <h2>Hola, Administrador 👋</h2>
        <p style="color: var(--text-dim); max-width: 600px;">
            Este es el centro de mando de la administración municipal. Desde aquí puedes acceder a las diferentes áreas y gestionar los recursos de manera eficiente.
        </p>
    </div>
</div>

<h2 class="section-title" style="font-size: 1rem; margin-bottom: 20px;">Dependencias Activas</h2>

<div class="dependency-grid">
    @foreach($areas as $area)
    @php
        $areaRoute = '#';
        if($area->slug == 'almacen') $areaRoute = route('almacen.index');
        elseif($area->slug == 'talento-humano') $areaRoute = route('hr.index');
        elseif($area->slug == 'gestion-riesgo') $areaRoute = route('risk.index');
        elseif($area->slug == 'obras-publicas') $areaRoute = route('planning.index');
    @endphp
    <a href="{{ $areaRoute }}" class="dep-card">
        <div class="dep-icon">
            {{ $area->icon ?: '🏢' }}
        </div>
        <div class="dep-info">
            <h3>{{ $area->name }}</h3>
            <p>{{ $area->description }}</p>
        </div>
        <div class="dep-footer">
            <div class="status-badge">
                <div class="dot"></div>
                Sistema Operativo
            </div>
            <div class="btn-access">Ingresar</div>
        </div>
    </a>
    @endforeach
</div>
@endsection
