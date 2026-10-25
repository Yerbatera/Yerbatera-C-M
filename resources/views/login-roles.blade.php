<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Yerbatera C&M</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f3f4f6;
        }
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 24px 16px;
        }
        button {
            display: block;
            margin: 15px auto;
            padding: 15px;
            width: 250px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            color: white;
        }
        footer {
            background-color: #15803d;
            border-top: 1px solid #16a34a;
            color: #f0fdf4;
            padding: 14px 24px;
            font-size: 12px;
        }
        .footer-inner {
            max-width: 72rem;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <main>
        <h1 style="color: #166534; margin-bottom: 8px;">Yerbatera C&M</h1>
        <p style="margin-top: 0; margin-bottom: 16px;">Seleccione su perfil de prueba</p>
        
        <form action="{{ route('login.rapido') }}" method="POST">
            @csrf <input type="hidden" name="rol" value="gerente"> <button style="background: #ea580c;">Ingresar como Gerente</button>
        </form>
        
        <form action="{{ route('login.rapido') }}" method="POST">
            @csrf <input type="hidden" name="rol" value="administracion"> <button style="background: #15803d;">Ingresar como Administración</button>
        </form>
        
        <form action="{{ route('login.rapido') }}" method="POST">
            @csrf <input type="hidden" name="rol" value="operario"> <button style="background: #22c55e;">Ingresar como Operario</button>
        </form>
        
        <form action="{{ route('producer.search') }}" method="GET">
            <button style="background: #7e22ce; color: white;" type="submit">Ingresar como Yerbatero</button>
        </form>
    </main>

    <footer>
        <div class="footer-inner">
            <span>Yerbatera C&M · Sistema de Gestión y Control Productivo</span>
            <span>Establecimiento Secadero · {{ date('Y') }}</span>
        </div>
    </footer>
</body>
</html>
