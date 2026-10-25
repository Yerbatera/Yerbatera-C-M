@extends('layouts.app')

@section('contenido')
<div class="container mx-auto px-4 pb-6">
    <!-- Cabecera con botón superior "Proveedores" -->
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-green-900">Registro de Balanza (Cupones y Remitos)</h1>
            <p class="text-sm text-gray-600">El comprobante RMT-AAAAMMDD-XXXX, la fecha/hora y el operario se asignan automáticamente.</p>
        </div>

        <button
            type="button"
            id="btn_abrir_modal_proveedor"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-900"
        >
            <span>Proveedores</span>
        </button>
    </div>

    <!-- Aviso dinámico con timer (éxito o error sin recargar la página) -->
    <div
        id="notificacion_balanza"
        class="mb-4 hidden rounded-lg border p-3 text-sm font-medium transition-opacity duration-300"
        role="status"
    ></div>

    @php
        $proveedorSeleccionado = old('id_proveedor', session('nuevo_proveedor_id'));
    @endphp

    <div class="mb-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-800">Nuevo pesaje</h2>

        <form id="form_nuevo_pesaje" action="{{ route('balanza.store') }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-2">
            @csrf

            <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                Proveedor (Productor / Yerbatero)
                <select
                    name="id_proveedor"
                    id="id_proveedor"
                    required
                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"
                >
                    <option value="" disabled @selected(! $proveedorSeleccionado) id="opcion_placeholder_proveedor">
                        {{ $proveedores->isEmpty() ? 'Sin proveedores cargados' : 'Seleccione un proveedor...' }}
                    </option>
                    @foreach ($proveedores as $proveedor)
                        <option value="{{ $proveedor->id }}" @selected((string) $proveedorSeleccionado === (string) $proveedor->id)>
                            {{ $proveedor->nombre }}@if ($proveedor->dni) (DNI: {{ $proveedor->dni }})@endif @if ($proveedor->telefono) · Tel: {{ $proveedor->telefono }}@endif
                        </option>
                    @endforeach
                </select>
                @error('id_proveedor')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                N° de Ticket (entregado por el Proveedor / Yerbatero)
                <input
                    type="text"
                    name="nro_ticket"
                    id="nro_ticket"
                    value="{{ old('nro_ticket') }}"
                    placeholder="Ej: TCK-0045A"
                    maxlength="100"
                    required
                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm uppercase"
                >
                @error('nro_ticket')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                Peso Bruto (kg)
                <input
                    type="number"
                    name="peso_bruto"
                    id="peso_bruto"
                    value="{{ old('peso_bruto') }}"
                    step="0.01"
                    min="0.01"
                    placeholder="0.00"
                    required
                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"
                >
                @error('peso_bruto')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                Tara (kg)
                <input
                    type="number"
                    name="tara"
                    id="tara"
                    value="{{ old('tara') }}"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    required
                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"
                >
                @error('tara')
                    <span class="text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                Peso Neto Calculado (kg)
                <input
                    type="text"
                    name="peso_neto"
                    id="peso_neto"
                    value="{{ old('peso_neto', '0.00') }}"
                    readonly
                    class="w-full cursor-not-allowed rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm font-semibold text-green-900"
                >
                <span id="alerta_peso_negativo" class="hidden text-xs text-red-600">
                    La tara no puede superar al peso bruto. Se ajustó el peso neto a 0.00 kg.
                </span>
            </label>

            <div class="flex items-end">
                <button
                    type="submit"
                    id="btn_guardar_pesaje"
                    class="w-full rounded-md bg-green-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-900 md:w-auto"
                >
                    Registrar Pesaje
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="p-4">Comprobante</th>
                        <th class="p-4">N° Ticket Prov.</th>
                        <th class="p-4">Fecha / Hora</th>
                        <th class="p-4">Proveedor</th>
                        <th class="p-4">Operario</th>
                        <th class="p-4 text-right">Bruto (kg)</th>
                        <th class="p-4 text-right">Tara (kg)</th>
                        <th class="p-4 text-right">Neto (kg)</th>
                    </tr>
                </thead>
                <tbody id="tabla_registros_balanza" class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($registros as $item)
                        <tr class="transition hover:bg-gray-50/50">
                            <td class="p-4 font-semibold text-green-900">{{ $item->remito }}</td>
                            <td class="p-4 font-mono text-gray-800">{{ $item->nro_ticket ?? '—' }}</td>
                            <td class="p-4">{{ $item->fecha_hora?->format('d/m/Y H:i:s') }}</td>
                            <td class="p-4" data-proveedor-cell="{{ $item->id_proveedor }}">{{ $item->proveedor?->nombre ?? '—' }}</td>
                            <td class="p-4">{{ $item->operario?->name ?? '—' }}</td>
                            <td class="p-4 text-right">{{ number_format((float) $item->peso_bruto, 2, ',', '.') }}</td>
                            <td class="p-4 text-right">{{ number_format((float) $item->peso_tara, 2, ',', '.') }}</td>
                            <td class="p-4 text-right font-semibold">{{ number_format((float) $item->peso_neto, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr id="fila_sin_pesajes">
                            <td colspan="8" class="p-8 text-center text-gray-400">Todavía no hay pesajes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Ventana Emergente (Modal) de Proveedores: Agregar y Ver/Editar Lista -->
<div
    id="modal_nuevo_proveedor"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4 backdrop-blur-xs"
    role="dialog"
    aria-modal="true"
    aria-labelledby="titulo_modal_proveedor"
>
    <div class="w-full max-w-2xl rounded-xl border border-gray-200 bg-white p-6 shadow-xl">
        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 id="titulo_modal_proveedor" class="text-lg font-bold text-green-900">
                Gestión de Productores / Proveedores
            </h3>
            <button
                type="button"
                id="btn_cerrar_modal_proveedor"
                class="rounded-md px-2 py-1 text-xs font-semibold text-gray-500 transition hover:bg-gray-100 hover:text-gray-800"
                aria-label="Cerrar ventana"
            >
                Cerrar
            </button>
        </div>

        <!-- Selector de pestañas del modal -->
        <div class="mb-4 flex gap-2 border-b border-gray-200 pb-3">
            <button
                type="button"
                id="tab_btn_formulario"
                class="rounded-lg bg-green-800 px-3 py-1.5 text-xs font-semibold text-white transition"
            >
                Agregar Productor
            </button>
            <button
                type="button"
                id="tab_btn_lista"
                class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-200"
            >
                Lista de Proveedores (<span id="contador_proveedores_modal">{{ $proveedores->count() }}</span>)
            </button>
        </div>

        <div id="error_modal_proveedor" class="mb-3 hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700"></div>

        <!-- Vista 1: Formulario (Alta o Edición) -->
        <div id="vista_modal_formulario">
            <form id="form_nuevo_proveedor" action="{{ route('balanza.proveedores.store') }}" method="POST" class="flex flex-col gap-4">
                @csrf
                <input type="hidden" id="modal_proveedor_id" value="">

                <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                    Nombre y Apellido / Razón Social *
                    <input
                        type="text"
                        name="nombre"
                        id="modal_proveedor_nombre"
                        required
                        maxlength="255"
                        placeholder="Ej: Juan Pérez"
                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"
                    >
                </label>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                        DNI / CUIT
                        <input
                            type="text"
                            name="dni"
                            id="modal_proveedor_dni"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="15"
                            placeholder="Ej: 30123456"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"
                        >
                    </label>

                    <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">
                        Teléfono
                        <input
                            type="text"
                            name="telefono"
                            id="modal_proveedor_telefono"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="20"
                            placeholder="Ej: 3764123456"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm"
                        >
                    </label>
                </div>

                <div class="mt-2 flex items-center justify-end gap-2 border-t border-gray-100 pt-4">
                    <button
                        type="button"
                        id="btn_cancelar_edicion_proveedor"
                        class="hidden rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200"
                    >
                        Volver a la lista
                    </button>
                    <button
                        type="button"
                        id="btn_cancelar_modal_proveedor"
                        class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200"
                    >
                        Cerrar
                    </button>
                    <button
                        type="submit"
                        id="btn_submit_modal_proveedor"
                        class="rounded-md bg-green-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-900"
                    >
                        Guardar Proveedor
                    </button>
                </div>
            </form>
        </div>

        <!-- Vista 2: Lista de Proveedores para ver y editar -->
        <div id="vista_modal_lista" class="hidden">
            <div class="max-h-72 overflow-y-auto rounded-lg border border-gray-200">
                <table class="w-full border-collapse text-left text-sm">
                    <thead class="sticky top-0 bg-gray-50 text-xs font-semibold uppercase text-gray-500">
                        <tr class="border-b border-gray-200">
                            <th class="p-3">Nombre</th>
                            <th class="p-3">DNI / CUIT</th>
                            <th class="p-3">Teléfono</th>
                            <th class="p-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tabla_modal_proveedores" class="divide-y divide-gray-100 text-gray-700">
                        @forelse ($proveedores as $proveedor)
                            <tr
                                data-proveedor-row="{{ $proveedor->id }}"
                                data-nombre="{{ $proveedor->nombre }}"
                                data-dni="{{ $proveedor->dni ?? '' }}"
                                data-telefono="{{ $proveedor->telefono ?? '' }}"
                                class="hover:bg-gray-50"
                            >
                                <td class="p-3 font-medium text-gray-900 col-nombre">{{ $proveedor->nombre }}</td>
                                <td class="p-3 col-dni">{{ $proveedor->dni ?? '—' }}</td>
                                <td class="p-3 col-telefono">{{ $proveedor->telefono ?? '—' }}</td>
                                <td class="p-3 text-right">
                                    <button
                                        type="button"
                                        class="btn-editar-proveedor rounded-md bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900 transition hover:bg-amber-200"
                                    >
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="fila_sin_proveedores_modal">
                                <td colspan="4" class="p-6 text-center text-gray-400">No hay proveedores registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const urlCrearProveedor = "{{ route('balanza.proveedores.store') }}";
    const urlBaseProveedores = "{{ url('/balanza/proveedores') }}";
    const csrfToken = "{{ csrf_token() }}";

    // --- Lógica de Ventana Emergente (Modal) para Proveedores ---
    const modalProveedor = document.getElementById('modal_nuevo_proveedor');
    const btnAbrirModal = document.getElementById('btn_abrir_modal_proveedor');
    const btnCerrarModal = document.getElementById('btn_cerrar_modal_proveedor');
    const btnCancelarModal = document.getElementById('btn_cancelar_modal_proveedor');
    const btnCancelarEdicion = document.getElementById('btn_cancelar_edicion_proveedor');

    const tabBtnFormulario = document.getElementById('tab_btn_formulario');
    const tabBtnLista = document.getElementById('tab_btn_lista');
    const vistaModalFormulario = document.getElementById('vista_modal_formulario');
    const vistaModalLista = document.getElementById('vista_modal_lista');
    const contadorProveedoresModal = document.getElementById('contador_proveedores_modal');
    const tablaModalProveedores = document.getElementById('tabla_modal_proveedores');

    const formNuevoProveedor = document.getElementById('form_nuevo_proveedor');
    const inputModalId = document.getElementById('modal_proveedor_id');
    const inputModalNombre = document.getElementById('modal_proveedor_nombre');
    const inputModalDni = document.getElementById('modal_proveedor_dni');
    const inputModalTelefono = document.getElementById('modal_proveedor_telefono');
    const errorModalProveedor = document.getElementById('error_modal_proveedor');
    const selectProveedor = document.getElementById('id_proveedor');
    const opcionPlaceholderProveedor = document.getElementById('opcion_placeholder_proveedor');
    const notificacionBalanza = document.getElementById('notificacion_balanza');
    const btnSubmitModalProveedor = document.getElementById('btn_submit_modal_proveedor');
    let timerNotificacion = null;

    function filtrarSoloNumeros(event) {
        event.target.value = event.target.value.replace(/\D+/g, '');
    }

    inputModalDni.addEventListener('input', filtrarSoloNumeros);
    inputModalTelefono.addEventListener('input', filtrarSoloNumeros);

    function mostrarAviso(mensaje, tipo = 'exito', milisegundos = 3000) {
        if (!notificacionBalanza) {
            return;
        }

        if (timerNotificacion) {
            clearTimeout(timerNotificacion);
        }

        notificacionBalanza.textContent = mensaje;
        notificacionBalanza.classList.remove(
            'hidden',
            'border-green-200',
            'bg-green-50',
            'text-green-800',
            'border-red-200',
            'bg-red-50',
            'text-red-800'
        );

        if (tipo === 'error') {
            notificacionBalanza.classList.add('border-red-200', 'bg-red-50', 'text-red-800');
        } else {
            notificacionBalanza.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
        }

        timerNotificacion = setTimeout(function () {
            notificacionBalanza.classList.add('hidden');
        }, milisegundos);
    }

    function prepararModoAgregar() {
        formNuevoProveedor.reset();
        inputModalId.value = '';
        tabBtnFormulario.textContent = 'Agregar Productor';
        btnSubmitModalProveedor.textContent = 'Guardar Proveedor';
        btnCancelarEdicion.classList.add('hidden');
        errorModalProveedor.classList.add('hidden');
    }

    function activarVistaFormulario(resetear = true) {
        if (resetear) {
            prepararModoAgregar();
        }
        vistaModalFormulario.classList.remove('hidden');
        vistaModalLista.classList.add('hidden');

        tabBtnFormulario.className = 'rounded-lg bg-green-800 px-3 py-1.5 text-xs font-semibold text-white transition';
        tabBtnLista.className = 'rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-200';
        inputModalNombre.focus();
    }

    function activarVistaLista() {
        prepararModoAgregar();
        vistaModalFormulario.classList.add('hidden');
        vistaModalLista.classList.remove('hidden');

        tabBtnLista.className = 'rounded-lg bg-green-800 px-3 py-1.5 text-xs font-semibold text-white transition';
        tabBtnFormulario.className = 'rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-200';
    }

    tabBtnFormulario.addEventListener('click', function () {
        activarVistaFormulario(true);
    });

    tabBtnLista.addEventListener('click', activarVistaLista);
    btnCancelarEdicion.addEventListener('click', activarVistaLista);

    function abrirModalProveedor() {
        errorModalProveedor.classList.add('hidden');
        errorModalProveedor.textContent = '';
        modalProveedor.classList.remove('hidden');
        modalProveedor.classList.add('flex');
        activarVistaFormulario(true);
    }

    function cerrarModalProveedor() {
        modalProveedor.classList.add('hidden');
        modalProveedor.classList.remove('flex');
        prepararModoAgregar();
    }

    btnAbrirModal.addEventListener('click', abrirModalProveedor);
    btnCerrarModal.addEventListener('click', cerrarModalProveedor);
    btnCancelarModal.addEventListener('click', cerrarModalProveedor);

    modalProveedor.addEventListener('click', function (event) {
        if (event.target === modalProveedor) {
            cerrarModalProveedor();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modalProveedor.classList.contains('hidden')) {
            cerrarModalProveedor();
        }
    });

    // Delegación de clic para el botón "Editar" en la tabla de proveedores del modal
    tablaModalProveedores.addEventListener('click', function (event) {
        const btnEditar = event.target.closest('.btn-editar-proveedor');
        if (!btnEditar) {
            return;
        }

        const fila = btnEditar.closest('tr');
        inputModalId.value = fila.dataset.proveedorRow;
        inputModalNombre.value = fila.dataset.nombre || '';
        inputModalDni.value = fila.dataset.dni || '';
        inputModalTelefono.value = fila.dataset.telefono || '';

        tabBtnFormulario.textContent = 'Editando Productor';
        btnSubmitModalProveedor.textContent = 'Guardar Cambios';
        btnCancelarEdicion.classList.remove('hidden');
        activarVistaFormulario(false);
    });

    formNuevoProveedor.addEventListener('submit', async function (event) {
        event.preventDefault();
        errorModalProveedor.classList.add('hidden');
        btnSubmitModalProveedor.disabled = true;

        const idEdicion = inputModalId.value;
        const esEdicion = Boolean(idEdicion);
        const urlDestino = esEdicion ? `${urlBaseProveedores}/${idEdicion}` : urlCrearProveedor;

        const formData = new FormData(formNuevoProveedor);
        if (esEdicion) {
            formData.append('_method', 'PUT');
        }

        try {
            const response = await fetch(urlDestino, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                const primerError = data?.errors ? Object.values(data.errors)[0][0] : (data?.message || 'No se pudo guardar el proveedor.');
                errorModalProveedor.textContent = primerError;
                errorModalProveedor.classList.remove('hidden');
                return;
            }

            if (opcionPlaceholderProveedor) {
                opcionPlaceholderProveedor.textContent = 'Seleccione un proveedor...';
            }

            if (esEdicion) {
                // Actualizar opción en el desplegable principal
                const opcionExistente = selectProveedor.querySelector(`option[value="${data.id}"]`);
                if (opcionExistente) {
                    opcionExistente.textContent = data.label;
                }

                // Actualizar fila en la lista del modal
                const filaModal = tablaModalProveedores.querySelector(`tr[data-proveedor-row="${data.id}"]`);
                if (filaModal) {
                    filaModal.dataset.nombre = data.nombre;
                    filaModal.dataset.dni = data.dni || '';
                    filaModal.dataset.telefono = data.telefono || '';
                    filaModal.querySelector('.col-nombre').textContent = data.nombre;
                    filaModal.querySelector('.col-dni').textContent = data.dni || '—';
                    filaModal.querySelector('.col-telefono').textContent = data.telefono || '—';
                }

                // Actualizar celdas en la tabla de pesajes recientes si corresponde
                document.querySelectorAll(`[data-proveedor-cell="${data.id}"]`).forEach(function (celda) {
                    celda.textContent = data.nombre;
                });

                mostrarAviso(data.message || 'Proveedor actualizado.', 'exito', 3000);
                activarVistaLista();
            } else {
                // Agregar nueva opción al desplegable y seleccionarla
                const nuevaOpcion = document.createElement('option');
                nuevaOpcion.value = data.id;
                nuevaOpcion.textContent = data.label;
                nuevaOpcion.selected = true;
                selectProveedor.appendChild(nuevaOpcion);
                selectProveedor.value = String(data.id);

                // Agregar fila a la tabla del modal
                const filaSinProveedores = document.getElementById('fila_sin_proveedores_modal');
                if (filaSinProveedores) {
                    filaSinProveedores.remove();
                }

                const nuevaFilaModal = document.createElement('tr');
                nuevaFilaModal.dataset.proveedorRow = data.id;
                nuevaFilaModal.dataset.nombre = data.nombre;
                nuevaFilaModal.dataset.dni = data.dni || '';
                nuevaFilaModal.dataset.telefono = data.telefono || '';
                nuevaFilaModal.className = 'hover:bg-gray-50';
                nuevaFilaModal.innerHTML = `
                    <td class="p-3 font-medium text-gray-900 col-nombre">${data.nombre}</td>
                    <td class="p-3 col-dni">${data.dni || '—'}</td>
                    <td class="p-3 col-telefono">${data.telefono || '—'}</td>
                    <td class="p-3 text-right">
                        <button type="button" class="btn-editar-proveedor rounded-md bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900 transition hover:bg-amber-200">
                            Editar
                        </button>
                    </td>
                `;
                tablaModalProveedores.appendChild(nuevaFilaModal);
                contadorProveedoresModal.textContent = String(tablaModalProveedores.querySelectorAll('tr[data-proveedor-row]').length);

                mostrarAviso(data.message || 'Proveedor guardado.', 'exito', 3000);
                cerrarModalProveedor();
            }
        } catch (error) {
            errorModalProveedor.textContent = 'Error de conexión al guardar el proveedor.';
            errorModalProveedor.classList.remove('hidden');
        } finally {
            btnSubmitModalProveedor.disabled = false;
        }
    });

    // --- Lógica de Cálculo Dinámico de Peso Neto ---
    const inputPesoBruto = document.getElementById('peso_bruto');
    const inputTara = document.getElementById('tara');
    const inputPesoNeto = document.getElementById('peso_neto');
    const alertaPesoNegativo = document.getElementById('alerta_peso_negativo');
    const btnGuardarPesaje = document.getElementById('btn_guardar_pesaje');

    function calcularPesoNeto() {
        const pesoBruto = Math.max(0, parseFloat(inputPesoBruto.value) || 0);
        const tara = Math.max(0, parseFloat(inputTara.value) || 0);
        const diferencia = pesoBruto - tara;

        if (diferencia < 0) {
            inputPesoNeto.value = '0.00';
            alertaPesoNegativo.classList.remove('hidden');
            btnGuardarPesaje.disabled = true;
            btnGuardarPesaje.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }

        alertaPesoNegativo.classList.add('hidden');
        btnGuardarPesaje.disabled = false;
        btnGuardarPesaje.classList.remove('opacity-50', 'cursor-not-allowed');
        inputPesoNeto.value = diferencia.toFixed(2);
    }

    inputPesoBruto.addEventListener('input', calcularPesoNeto);
    inputTara.addEventListener('input', calcularPesoNeto);
    calcularPesoNeto();

    // --- Envío de Pesaje sin recargar la página ---
    const formNuevoPesaje = document.getElementById('form_nuevo_pesaje');
    const tablaRegistrosBalanza = document.getElementById('tabla_registros_balanza');
    const inputNroTicket = document.getElementById('nro_ticket');

    formNuevoPesaje.addEventListener('submit', async function (event) {
        event.preventDefault();
        btnGuardarPesaje.disabled = true;

        try {
            const response = await fetch(formNuevoPesaje.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(formNuevoPesaje),
            });

            const data = await response.json();

            if (!response.ok) {
                const mensajeError = data?.errors
                    ? Object.values(data.errors)[0][0]
                    : (data?.message || 'Error al registrar el pesaje.');
                mostrarAviso(mensajeError, 'error', 4000);
                return;
            }

            const filaSinPesajes = document.getElementById('fila_sin_pesajes');
            if (filaSinPesajes) {
                filaSinPesajes.remove();
            }

            if (data.registro && tablaRegistrosBalanza) {
                const idProvActual = selectProveedor.value;
                const nuevaFila = document.createElement('tr');
                nuevaFila.className = 'transition hover:bg-gray-50/50';
                nuevaFila.innerHTML = `
                    <td class="p-4 font-semibold text-green-900">${data.registro.remito}</td>
                    <td class="p-4 font-mono text-gray-800">${data.registro.nro_ticket}</td>
                    <td class="p-4">${data.registro.fecha_hora}</td>
                    <td class="p-4" data-proveedor-cell="${idProvActual}">${data.registro.proveedor}</td>
                    <td class="p-4">${data.registro.operario}</td>
                    <td class="p-4 text-right">${data.registro.peso_bruto}</td>
                    <td class="p-4 text-right">${data.registro.peso_tara}</td>
                    <td class="p-4 text-right font-semibold">${data.registro.peso_neto}</td>
                `;
                tablaRegistrosBalanza.prepend(nuevaFila);
            }

            inputNroTicket.value = '';
            inputPesoBruto.value = '';
            inputTara.value = '';
            calcularPesoNeto();

            mostrarAviso(data.message || 'Pesaje registrado.', 'exito', 3000);
        } catch (error) {
            mostrarAviso('Error de conexión con el servidor al registrar el pesaje.', 'error', 4000);
        } finally {
            btnGuardarPesaje.disabled = false;
        }
    });
</script>
@endsection
