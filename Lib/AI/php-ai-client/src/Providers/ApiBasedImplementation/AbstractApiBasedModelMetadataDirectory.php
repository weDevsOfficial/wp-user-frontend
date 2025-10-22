<?php

declare(strict_types=1);

namespace WordPress\AiClient\Providers\ApiBasedImplementation;

use InvalidArgumentException;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Http\Contracts\WithHttpTransporterInterface;
use WordPress\AiClient\Providers\Http\Contracts\WithRequestAuthenticationInterface;
use WordPress\AiClient\Providers\Http\Traits\WithHttpTransporterTrait;
use WordPress\AiClient\Providers\Http\Traits\WithRequestAuthenticationTrait;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;

/**
 * Base class for an API-based model metadata directory for a provider.
 *
 * @since 0.1.0
 */
abstract class AbstractApiBasedModelMetadataDirectory implements
    ModelMetadataDirectoryInterface,
    WithHttpTransporterInterface,
    WithRequestAuthenticationInterface
{
    use WithHttpTransporterTrait;
    use WithRequestAuthenticationTrait;

    /**
     * @var ?array<string, ModelMetadata> Map of model ID to model metadata, effectively for caching.
     */
    private ?array $modelMetadataMap = null;

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    final public function listModelMetadata(): array
    {
        $modelsMetadata = $this->getModelMetadataMap();
        return array_values($modelsMetadata);
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    final public function hasModelMetadata(string $modelId): bool
    {
        $modelsMetadata = $this->getModelMetadataMap();
        return isset($modelsMetadata[$modelId]);
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    final public function getModelMetadata(string $modelId): ModelMetadata
    {
        $modelsMetadata = $this->getModelMetadataMap();
        if (!isset($modelsMetadata[$modelId])) {
            throw new InvalidArgumentException(
                sprintf('No model with ID %s was found in the provider', $modelId)
            );
        }
        return $modelsMetadata[$modelId];
    }

    /**
     * Returns the map of model ID to model metadata for all models from the provider.
     *
     * @since 0.1.0
     *
     * @return array<string, ModelMetadata> Map of model ID to model metadata.
     */
    private function getModelMetadataMap(): array
    {
        if ($this->modelMetadataMap === null) {
            $this->modelMetadataMap = $this->sendListModelsRequest();
        }
        return $this->modelMetadataMap;
    }

    /**
     * Sends the API request to list models from the provider and returns the map of model ID to model metadata.
     *
     * @since 0.1.0
     *
     * @return array<string, ModelMetadata> Map of model ID to model metadata.
     */
    abstract protected function sendListModelsRequest(): array;
}
