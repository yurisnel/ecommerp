<?php

namespace App\Http\Controllers;

use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

/**
 * Base Controller
 * Provides standard CRUD operations for all controllers
 */
abstract class BaseController extends Controller
{
    /**
     * @var BaseService
     */
    protected $service;

    /**
     * BaseController constructor.
     * @param BaseService $service
     */
    public function __construct(BaseService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $filters = $request->except(['page', 'per_page', 'include', 'sort', 'order']);

        if ($perPage == -1) {
            // Return all records without pagination
            $data = $this->service->getAll();
        } elseif (!empty($filters)) {
            $data = $this->service->search($filters, $perPage);
        } else {
            $data = $this->service->paginate($perPage);
        }

        return $this->successResponse($data, 'Records retrieved successfully');
    }

    /**
     * Display the specified resource
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->service->getById($id);

        if (!$data) {
            return $this->errorResponse('Record not found', 404);
        }

        return $this->successResponse($data, 'Record retrieved successfully');
    }

    /**
     * Store a newly created resource
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request);
        $data = $this->service->create($validated);

        return $this->successResponse($data, 'Record created successfully', 201);
    }

    /**
     * Update the specified resource
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $this->validateRequest($request, $id);
        $result = $this->service->update($id, $validated);

        if (!$result) {
            return $this->errorResponse('Record not found', 404);
        }

        $data = $this->service->getById($id);
        return $this->successResponse($data, 'Record updated successfully');
    }

    /**
     * Remove the specified resource
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->service->delete($id);

        if (!$result) {
            return $this->errorResponse('Record not found', 404);
        }

        return $this->successResponse(null, 'Record deleted successfully');
    }

    /**
     * Validate request data
     * Override this method in child classes for specific validation rules
     * 
     * @param Request $request
     * @param int|null $id
     * @return array
     */
    abstract protected function validateRequest(Request $request, ?int $id = null): array;

    /**
     * Success response helper
     * 
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse($data, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Error response helper
     * 
     * @param string $message
     * @param int $code
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Error', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
