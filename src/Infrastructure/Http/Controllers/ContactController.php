<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controllers;

use Application\DTOs\CreateContactDTO;
use Application\DTOs\UpdateContactDTO;
use Application\UseCases\Contact\CreateContactUseCase;
use Application\UseCases\Contact\DeleteContactUseCase;
use Application\UseCases\Contact\GetContactUseCase;
use Application\UseCases\Contact\ListContactsUseCase;
use Application\UseCases\Contact\TriggerScoreProcessingUseCase;
use Application\UseCases\Contact\UpdateContactUseCase;
use Domain\Contact\Exceptions\ContactNotFoundException;
use Domain\Contact\Exceptions\ContactNotProcessableException;
use Infrastructure\Http\Requests\CreateContactRequest;
use Infrastructure\Http\Requests\UpdateContactRequest;
use Infrastructure\Http\Resources\ContactResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class ContactController extends Controller
{
    public function __construct(
        private readonly CreateContactUseCase $createContactUseCase,
        private readonly UpdateContactUseCase $updateContactUseCase,
        private readonly DeleteContactUseCase $deleteContactUseCase,
        private readonly GetContactUseCase $getContactUseCase,
        private readonly ListContactsUseCase $listContactsUseCase,
        private readonly TriggerScoreProcessingUseCase $triggerScoreProcessingUseCase,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 15);

        $result = $this->listContactsUseCase->execute($page, $perPage);

        return response()->json([
            'data' => ContactResource::collection(collect($result['data']))->toArray($request),
            'meta' => $result['meta'],
        ]);
    }

    public function store(CreateContactRequest $request): JsonResponse
    {
        try {
            $contact = $this->createContactUseCase->execute(
                new CreateContactDTO(
                    name: $request->string('name')->value(),
                    email: $request->string('email')->value(),
                    phone: $request->string('phone')->value(),
                )
            );

            return response()->json(
                (new ContactResource($contact))->toArray($request),
                201
            );
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $contact = $this->getContactUseCase->execute($id);
            return response()->json((new ContactResource($contact))->toArray($request));
        } catch (ContactNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function update(UpdateContactRequest $request, int $id): JsonResponse
    {
        try {
            $contact = $this->updateContactUseCase->execute(
                new UpdateContactDTO(
                    id: $id,
                    name: $request->string('name')->value(),
                    email: $request->string('email')->value(),
                    phone: $request->string('phone')->value(),
                )
            );

            return response()->json((new ContactResource($contact))->toArray($request));
        } catch (ContactNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->deleteContactUseCase->execute($id);
            return response()->json(null, 204);
        } catch (ContactNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function processScore(int $id): JsonResponse
    {
        try {
            $this->triggerScoreProcessingUseCase->execute($id);
            return response()->json(['message' => 'Score processing has been queued.']);
        } catch (ContactNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (ContactNotProcessableException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
