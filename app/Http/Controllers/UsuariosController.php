<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Services\UserCreationService;
use App\Services\Contracts\ApiClientFactoryInterface;
use App\Services\CalendlyUserImporter;
use App\Services\UserSynchronizationService;

class UsuariosController extends Controller
{
    private ApiClientFactoryInterface $apiFactory;
    private UserSynchronizationService $userSynchronizationService;
    private UserCreationService $userService;

    public function __construct(ApiClientFactoryInterface $apiFactory)
    {
        $this->apiFactory = $apiFactory;

        $calendlyImporter = new CalendlyUserImporter($this->apiFactory);
        $this->userService = new UserCreationService();
        $this->userSynchronizationService = new UserSynchronizationService($calendlyImporter, $this->userService);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = User::with('roles')->where('id', '!=', 1);

            return DataTables::of($users)
                ->addColumn('action', function ($user) {
                    $editUrl = route('usuarios.form', $user->id);

                    $buttons = '<div class="d-flex justify-content-center align-items-center">';
                    $buttons .= '<a href="' . $editUrl . '" class="btn btn-outline-info btn-sm" title="Editar">';
                    $buttons .= '<i class="bi bi-pencil"></i>';
                    $buttons .= '</a>';
                    $buttons .= '</div>';

                    return $buttons;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
   $this->importar_usuarios();
        return view('usuarios.usuarios_index');
    }

    public function importar_usuarios()
    {
        $report = $this->userSynchronizationService->synchronizeUsers();

        return response()->json([
            'message' => 'Proceso de importación de usuarios completado.',
            'imported_users' => $report['imported'],
            'skipped_users' => $report['skipped'],
            'errors' => $report['errors'],
        ]);
    }

    public function form(User $user = null)
    {
        return view('usuarios.usuarios_form', [
            'user' => $user ?? new User()
        ]);
    }

    public function guardar(Request $request)
    {
        $user = $request->id ? User::findOrFail($request->id) : null;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users')->ignore($user?->id)
            ],
            'password' => $user ? ['nullable', 'string', 'min:6'] : ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'uuid' => ['nullable', 'string'],
            'locale' => ['nullable', 'string', 'max:10'],
            'time_notation' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'slug' => ['nullable', 'string', 'max:255'],
            'scheduling_url' => ['nullable', 'string', 'max:255'],
            'calendly_uri' => ['nullable', 'string', 'max:255'],
        ];

        $messages = [
            'required' => 'Este campo es obligatorio.',
            'email' => 'Debe ser un correo válido.',
            'max' => 'No debe superar los :max caracteres.',
            'unique' => 'Ya existe un usuario con este correo.',
            'min' => 'Debe tener al menos :min caracteres.',
            'exists' => 'El rol seleccionado no es válido.',
        ];

        $validated = $request->validate($rules, $messages);

        // Extraer el rol para manejar por separado
        $role = $validated['role'];
        unset($validated['role']);

        if ($user) {
            unset($validated['email']);
            $this->userService->update($user, $validated);
            // Asignar el rol al usuario existente
            $user->syncRoles([$role]);
        } else {
            $newUser = $this->userService->create($validated);
            // Asignar el rol al nuevo usuario
            $newUser->assignRole($role);
        }

        return redirect()->route('usuarios')->with('success', 'Usuario guardado correctamente.');
    }
}
