<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpaceRequest;
use App\Http\Requests\UpdateSpaceRequest;
use App\Http\Resources\SpaceCollection;
use App\Http\Resources\SpaceResource;
use App\Services\SpaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SpaceController extends Controller
{
    public function __construct(
        protected SpaceService $spaceService
    ) {
    }

    /**
     * Display a listing of spaces with OData support.
     *
     * Supports OData query parameters:
     * - $filter: Filter results (e.g., substringof('text', name))
     * - $orderby: Order results (e.g., name asc, price_per_hour desc)
     * - $top: Limit results (pagination size)
     * - $skip: Skip results (pagination offset)
     *
     * @param Request $request
     * @return SpaceCollection
     */
    public function index(Request $request): SpaceCollection
    {
        $queryParams = $request->query();
        $spaces = $this->spaceService->getSpaces($queryParams);

        return new SpaceCollection($spaces);
    }

    /**
     * Store a newly created space.
     *
     * @param StoreSpaceRequest $request
     * @return JsonResponse
     */
    public function store(StoreSpaceRequest $request): JsonResponse
    {
        $space = $this->spaceService->createSpace($request->validated());

        return (new SpaceResource($space))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified space.
     *
     * @param int $id
     * @return SpaceResource|JsonResponse
     */
    public function show(int $id): SpaceResource|JsonResponse
    {
        $space = $this->spaceService->getSpace($id);

        if (!$space) {
            return response()->json([
                'message' => 'Space not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $resource = new SpaceResource($space);
        $resource->withoutWrapping();

        return $resource;
    }

    /**
     * Update the specified space.
     *
     * @param UpdateSpaceRequest $request
     * @param int $id
     * @return SpaceResource|JsonResponse
     */
    public function update(UpdateSpaceRequest $request, int $id): SpaceResource|JsonResponse
    {
        $space = $this->spaceService->getSpace($id);

        if (!$space) {
            return response()->json([
                'message' => 'Space not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $updatedSpace = $this->spaceService->updateSpace($space, $request->validated());

        return new SpaceResource($updatedSpace);
    }

    /**
     * Remove the specified space.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $space = $this->spaceService->getSpace($id);

        if (!$space) {
            return response()->json([
                'message' => 'Space not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->spaceService->deleteSpace($space);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
