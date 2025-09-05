<?php

declare(strict_types=1);

namespace WordPress\AiClient\Providers\DTO;

use InvalidArgumentException;
use WordPress\AiClient\Common\AbstractDataTransferObject;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;

/**
 * Represents metadata about a provider and its available models.
 *
 * This class combines provider information with the models that
 * the provider offers, facilitating model discovery and selection.
 *
 * @since 0.1.0
 *
 * @phpstan-import-type ProviderMetadataArrayShape from ProviderMetadata
 * @phpstan-import-type ModelMetadataArrayShape from ModelMetadata
 *
 * @phpstan-type ProviderModelsMetadataArrayShape array{
 *     provider: ProviderMetadataArrayShape,
 *     models: list<ModelMetadataArrayShape>
 * }
 *
 * @extends AbstractDataTransferObject<ProviderModelsMetadataArrayShape>
 */
class ProviderModelsMetadata extends AbstractDataTransferObject
{
    public const KEY_PROVIDER = 'provider';
    public const KEY_MODELS = 'models';

    /**
     * @var ProviderMetadata The provider metadata.
     */
    protected ProviderMetadata $provider;

    /**
     * @var list<ModelMetadata> The available models.
     */
    protected array $models;

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param ProviderMetadata $provider The provider metadata.
     * @param list<ModelMetadata> $models The available models.
     *
     * @throws InvalidArgumentException If models is not a list.
     */
    public function __construct(ProviderMetadata $provider, array $models)
    {
        if (!array_is_list($models)) {
            throw new InvalidArgumentException('Models must be a list array.');
        }

        $this->provider = $provider;
        $this->models = $models;
    }

    /**
     * Gets the provider metadata.
     *
     * @since 0.1.0
     *
     * @return ProviderMetadata The provider metadata.
     */
    public function getProvider(): ProviderMetadata
    {
        return $this->provider;
    }

    /**
     * Gets the available models.
     *
     * @since 0.1.0
     *
     * @return list<ModelMetadata> The available models.
     */
    public function getModels(): array
    {
        return $this->models;
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    public static function getJsonSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                self::KEY_PROVIDER => ProviderMetadata::getJsonSchema(),
                self::KEY_MODELS => [
                    'type' => 'array',
                    'items' => ModelMetadata::getJsonSchema(),
                    'description' => 'The available models for this provider.',
                ],
            ],
            'required' => [self::KEY_PROVIDER, self::KEY_MODELS],
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     *
     * @return ProviderModelsMetadataArrayShape
     */
    public function toArray(): array
    {
        return [
            self::KEY_PROVIDER => $this->provider->toArray(),
            self::KEY_MODELS => array_map(
                static fn(ModelMetadata $model): array => $model->toArray(),
                $this->models
            ),
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    public static function fromArray(array $array): self
    {
        static::validateFromArrayData($array, [self::KEY_PROVIDER, self::KEY_MODELS]);

        return new self(
            ProviderMetadata::fromArray($array[self::KEY_PROVIDER]),
            array_map(
                static fn(array $modelData): ModelMetadata => ModelMetadata::fromArray($modelData),
                $array[self::KEY_MODELS]
            )
        );
    }
}
