<?php

declare(strict_types=1);

namespace WordPress\AiClient\Providers\Models\DTO;

use InvalidArgumentException;
use WordPress\AiClient\Common\AbstractDataTransferObject;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;

/**
 * Represents metadata about an AI model.
 *
 * This class contains information about a specific AI model, including
 * its identifier, display name, supported capabilities, and configuration options.
 *
 * @since 0.1.0
 *
 * @phpstan-import-type SupportedOptionArrayShape from SupportedOption
 *
 * @phpstan-type ModelMetadataArrayShape array{
 *     id: string,
 *     name: string,
 *     supportedCapabilities: list<string>,
 *     supportedOptions: list<SupportedOptionArrayShape>
 * }
 *
 * @extends AbstractDataTransferObject<ModelMetadataArrayShape>
 */
class ModelMetadata extends AbstractDataTransferObject
{
    public const KEY_ID = 'id';
    public const KEY_NAME = 'name';
    public const KEY_SUPPORTED_CAPABILITIES = 'supportedCapabilities';
    public const KEY_SUPPORTED_OPTIONS = 'supportedOptions';

    /**
     * @var string The model's unique identifier.
     */
    protected string $id;

    /**
     * @var string The model's display name.
     */
    protected string $name;

    /**
     * @var list<CapabilityEnum> The model's supported capabilities.
     */
    protected array $supportedCapabilities;

    /**
     * @var list<SupportedOption> The model's supported configuration options.
     */
    protected array $supportedOptions;

    /**
     * @var array<string, true> Map of supported capabilities for O(1) lookups.
     */
    private array $capabilitiesMap = [];

    /**
     * @var array<string, SupportedOption> Map of supported options by name for O(1) lookups.
     */
    private array $optionsMap = [];

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param string $id The model's unique identifier.
     * @param string $name The model's display name.
     * @param list<CapabilityEnum> $supportedCapabilities The model's supported capabilities.
     * @param list<SupportedOption> $supportedOptions The model's supported configuration options.
     *
     * @throws InvalidArgumentException If arrays are not lists.
     */
    public function __construct(string $id, string $name, array $supportedCapabilities, array $supportedOptions)
    {
        if (!array_is_list($supportedCapabilities)) {
            throw new InvalidArgumentException('Supported capabilities must be a list array.');
        }

        if (!array_is_list($supportedOptions)) {
            throw new InvalidArgumentException('Supported options must be a list array.');
        }

        $this->id = $id;
        $this->name = $name;
        $this->supportedCapabilities = $supportedCapabilities;
        $this->supportedOptions = $supportedOptions;

        // Build capability map for efficient lookups
        foreach ($supportedCapabilities as $capability) {
            $this->capabilitiesMap[$capability->value] = true;
        }

        // Build options map for efficient lookups
        foreach ($supportedOptions as $option) {
            $this->optionsMap[$option->getName()->value] = $option;
        }
    }

    /**
     * Gets the model's unique identifier.
     *
     * @since 0.1.0
     *
     * @return string The model ID.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Gets the model's display name.
     *
     * @since 0.1.0
     *
     * @return string The model name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the model's supported capabilities.
     *
     * @since 0.1.0
     *
     * @return list<CapabilityEnum> The supported capabilities.
     */
    public function getSupportedCapabilities(): array
    {
        return $this->supportedCapabilities;
    }

    /**
     * Gets the model's supported configuration options.
     *
     * @since 0.1.0
     *
     * @return list<SupportedOption> The supported options.
     */
    public function getSupportedOptions(): array
    {
        return $this->supportedOptions;
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
                self::KEY_ID => [
                    'type' => 'string',
                    'description' => 'The model\'s unique identifier.',
                ],
                self::KEY_NAME => [
                    'type' => 'string',
                    'description' => 'The model\'s display name.',
                ],
                self::KEY_SUPPORTED_CAPABILITIES => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                        'enum' => CapabilityEnum::getValues(),
                    ],
                    'description' => 'The model\'s supported capabilities.',
                ],
                self::KEY_SUPPORTED_OPTIONS => [
                    'type' => 'array',
                    'items' => SupportedOption::getJsonSchema(),
                    'description' => 'The model\'s supported configuration options.',
                ],
            ],
            'required' => [self::KEY_ID, self::KEY_NAME, self::KEY_SUPPORTED_CAPABILITIES, self::KEY_SUPPORTED_OPTIONS],
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     *
     * @return ModelMetadataArrayShape
     */
    public function toArray(): array
    {
        return [
            self::KEY_ID => $this->id,
            self::KEY_NAME => $this->name,
            self::KEY_SUPPORTED_CAPABILITIES => array_map(
                static fn(CapabilityEnum $capability): string => $capability->value,
                $this->supportedCapabilities
            ),
            self::KEY_SUPPORTED_OPTIONS => array_map(
                static fn(SupportedOption $option): array => $option->toArray(),
                $this->supportedOptions
            ),
        ];
    }

    /**
     * Checks whether this model meets the specified requirements.
     *
     * @since 0.1.0
     *
     * @param ModelRequirements $requirements The requirements to check against.
     * @return bool True if the model meets all requirements, false otherwise.
     */
    public function meetsRequirements(ModelRequirements $requirements): bool
    {
        // Check if all required capabilities are supported using map lookup
        foreach ($requirements->getRequiredCapabilities() as $requiredCapability) {
            if (!isset($this->capabilitiesMap[$requiredCapability->value])) {
                return false;
            }
        }

        // Check if all required options are supported with the specified values
        foreach ($requirements->getRequiredOptions() as $requiredOption) {
            // Use map lookup instead of linear search
            if (!isset($this->optionsMap[$requiredOption->getName()->value])) {
                return false;
            }

            $supportedOption = $this->optionsMap[$requiredOption->getName()->value];

            // Check if the required value is supported by this option
            if (!$supportedOption->isSupportedValue($requiredOption->getValue())) {
                return false;
            }
        }

        return true;
    }


    /**
     * {@inheritDoc}
     *
     * @since 0.1.0
     */
    public static function fromArray(array $array): self
    {
        static::validateFromArrayData($array, [
            self::KEY_ID,
            self::KEY_NAME,
            self::KEY_SUPPORTED_CAPABILITIES,
            self::KEY_SUPPORTED_OPTIONS,
        ]);

        return new self(
            $array[self::KEY_ID],
            $array[self::KEY_NAME],
            array_map(
                static fn(string $capability): CapabilityEnum => CapabilityEnum::from($capability),
                $array[self::KEY_SUPPORTED_CAPABILITIES]
            ),
            array_map(
                static fn(array $optionData): SupportedOption => SupportedOption::fromArray($optionData),
                $array[self::KEY_SUPPORTED_OPTIONS]
            )
        );
    }
}
