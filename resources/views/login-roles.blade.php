<!DOCTYPE html>
<html lang="es">
<head>
    <title>Acceso Yerbatera C&M</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; background-color: #f3f4f6; }
        button { display: block; margin: 15px auto; padding: 15px; width: 250px; font-size: 16px; font-weight: bold; cursor: pointer; border: none; border-radius: 5px; color: white;}
    </style>
</head>
<body>
    <h1 style="color: #166534;">Yerbatera C&M</h1>
    <p>Seleccione su perfil de prueba</p>
    
    <form action="{{ route('login.rapido') }}" method="POST">
        @csrf <input type="hidden" name="rol" value="gerente"> <button style="background: #ea580c;">Ingresar como Gerente</button>
    </form>
    
    <form action="{{ route('login.rapido') }}" method="POST">
        @csrf <input type="hidden" name="rol" value="administracion"> <button style="background: #15803d;">Ingresar como Administración</button>
    </form>
    
    <form action="{{ route('login.rapido') }}" method="POST">
        @csrf <input type="hidden" name="rol" value="operario"> <button style="background: #22c55e;">Ingresar como Operario</button>
    </form>
    
    <form action="{{ route('login.rapido') }}" method="POST">
        @csrf <input type="hidden" name="rol" value="yerbatero"> <button style="background: #9333ea;">Ingresar como Yerbatero</button>
    </form>
</body>
</html>