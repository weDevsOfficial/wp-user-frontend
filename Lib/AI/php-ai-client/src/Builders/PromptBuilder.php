<?php

declare(strict_types=1);

namespace WordPress\AiClient\Builders;

use InvalidArgumentException;
use RuntimeException;
use WordPress\AiClient\Files\DTO\File;
use WordPress\AiClient\Files\Enums\FileTypeEnum;
use WordPress\AiClient\Messages\DTO\Message;
use WordPress\AiClient\Messages\DTO\MessagePart;
use WordPress\AiClient\Messages\DTO\UserMessage;
use WordPress\AiClient\Messages\Enums\MessageRoleEnum;
use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\Models\Contracts\ModelInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelConfig;
use WordPress\AiClient\Providers\Models\DTO\ModelRequirements;
use WordPress\AiClient\Providers\Models\DTO\RequiredOption;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;
use WordPress\AiClient\Providers\Models\ImageGeneration\Contracts\ImageGenerationModelInterface;
use WordPress\AiClient\Providers\Models\SpeechGeneration\Contracts\SpeechGenerationModelInterface;
use WordPress\AiClient\Providers\Models\TextGeneration\Contracts\TextGenerationModelInterface;
use WordPress\AiClient\Providers\Models\TextToSpeechConversion\Contracts\TextToSpeechConversionModelInterface;
use WordPress\AiClient\Providers\ProviderRegistry;
use WordPress\AiClient\Results\DTO\GenerativeAiResult;
use WordPress\AiClient\Tools\DTO\FunctionDeclaration;
use WordPress\AiClient\Tools\DTO\FunctionResponse;
use WordPress\AiClient\Tools\DTO\WebSearch;

/**
 * Fluent builder for constructing AI prompts.
 *
 * This class provides a fluent interface for building prompts with various
 * content types and model configurations. It automatically infers model
 * requirements based on the features used in the prompt.
 *
 * @since 0.1.0
 *
 * @phpstan-import-type MessageArrayShape from Message
 * @phpstan-import-type MessagePartArrayShape from MessagePart
 *
 * @phpstan-type Prompt string|MessagePart|Message|MessageArrayShape|list<string|MessagePart|MessagePartArrayShape>|list<Message>|null
 */
class PromptBuilder
{
    /**
     * @var ProviderRegistry The provider registry for finding suitable models.
     */
    private ProviderRegistry $registry;

    /**
     * @var list<Message> The messages in the conversation.
     */
    protected array $messages = [];

    /**
     * @var ModelInterface|null The model to use for generation.
     */
    protected ?ModelInterface $model = null;

    /**
     * @var string|null The provider ID or class name.
     */
    protected ?string $providerIdOrClassName = null;

    /**
     * @var ModelConfig The model configuration.
     */
    protected ModelConfig $modelConfig;

    // phpcs:disable Generic.Files.LineLength.TooLong
    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param ProviderRegistry $registry The provider registry for finding suitable models.
     * @param Prompt $prompt Optional initial prompt content.
     */
    // phpcs:enable Generic.Files.LineLength.TooLong
    public function __construct(ProviderRegistry $registry, $prompt = null)
    {
        $this->registry = $registry;
        $this->modelConfig = new ModelConfig();

        if ($prompt === null) {
            return;
        }

        // Check if it's a list of Messages - set as messages
        if ($this->isMessagesList($prompt)) {
            $this->messages = $prompt;
            return;
        }

        // Parse it as a user message
        $userMessage = $this->parseMessage($prompt, MessageRoleEnum::user());
        $this->messages[] = $userMessage;
    }

    /**
     * Adds text to the current message.
     *
     * @since 0.1.0
     *
     * @param string $text The text to add.
     * @return self
     */
    public function withText(string $text): self
    {
        $part = new MessagePart($text);
        $this->appendPartToMessages($part);
        return $this;
    }

    /**
     * Adds a file to the current message.
     *
     * Accepts:
     * - File object
     * - URL string (remote file)
     * - Base64-encoded data string
     * - Data URI string (data:mime/type;base64,data)
     * - Local file path string
     *
     * @since 0.1.0
     *
     * @param string|File $file The file (File object or string representation).
     * @param string|null $mimeType The MIME type (optional, ignored if File object provided).
     * @return self
     * @throws InvalidArgumentException If the file is invalid or MIME type cannot be determined.
     */
    public function withFile($file, ?string $mimeType = null): self
    {
        $file = $file instanceof File ? $file : new File($file, $mimeType);
        $part = new MessagePart($file);
        $this->appendPartToMessages($part);
        return $this;
    }


    /**
     * Adds a function response to the current message.
     *
     * @since 0.1.0
     *
     * @param FunctionResponse $functionResponse The function response.
     * @return self
     */
    public function withFunctionResponse(FunctionResponse $functionResponse): self
    {
        $part = new MessagePart($functionResponse);
        $this->appendPartToMessages($part);
        return $this;
    }

    /**
     * Adds message parts to the current message.
     *
     * @since 0.1.0
     *
     * @param MessagePart ...$parts The message parts to add.
     * @return self
     */
    public function withMessageParts(MessagePart ...$parts): self
    {
        foreach ($parts as $part) {
            $this->appendPartToMessages($part);
        }
        return $this;
    }

    /**
     * Adds conversation history messages.
     *
     * Historical messages are prepended to the beginning of the message list,
     * before the current message being built.
     *
     * @since 0.1.0
     *
     * @param Message ...$messages The messages to add to history.
     * @return self
     */
    public function withHistory(Message ...$messages): self
    {
        // Prepend the history messages to the beginning of the messages array
        $this->messages = array_merge($messages, $this->messages);

        return $this;
    }

    /**
     * Sets the model to use for generation.
     *
     * The model's configuration will be merged with the builder's configuration,
     * with the builder's configuration taking precedence for any overlapping settings.
     *
     * @since 0.1.0
     *
     * @param ModelInterface $model The model to use.
     * @return self
     */
    public function usingModel(ModelInterface $model): self
    {
        $this->model = $model;

        // Merge model's config with builder's config, with builder's config taking precedence
        $modelConfigArray = $model->getConfig()->toArray();
        $builderConfigArray = $this->modelConfig->toArray();
        $mergedConfigArray = array_merge($modelConfigArray, $builderConfigArray);

        $this->modelConfig = ModelConfig::fromArray($mergedConfigArray);

        return $this;
    }

    /**
     * Sets the model configuration.
     *
     * Merges the provided configuration with the builder's configuration,
     * with builder configuration taking precedence.
     *
     * @since 0.1.0
     *
     * @param ModelConfig $config The model configuration to merge.
     * @return self
     */
    public function usingModelConfig(ModelConfig $config): self
    {
        // Convert both configs to arrays
        $builderConfigArray = $this->modelConfig->toArray();
        $providedConfigArray = $config->toArray();

        // Merge arrays with builder config taking precedence
        $mergedArray = array_merge($providedConfigArray, $builderConfigArray);

        // Create new config from merged array
        $this->modelConfig = ModelConfig::fromArray($mergedArray);

        return $this;
    }

    /**
     * Sets the provider to use for generation.
     *
     * @since 0.1.0
     *
     * @param string $providerIdOrClassName The provider ID or class name.
     * @return self
     */
    public function usingProvider(string $providerIdOrClassName): self
    {
        $this->providerIdOrClassName = $providerIdOrClassName;
        return $this;
    }

    /**
     * Sets the system instruction.
     *
     * System instructions are stored in the model configuration and guide
     * the AI model's behavior throughout the conversation.
     *
     * @since 0.1.0
     *
     * @param string $systemInstruction The system instruction text.
     * @return self
     */
    public function usingSystemInstruction(string $systemInstruction): self
    {
        $this->modelConfig->setSystemInstruction($systemInstruction);
        return $this;
    }

    /**
     * Sets the maximum number of tokens to generate.
     *
     * @since 0.1.0
     *
     * @param int $maxTokens The maximum number of tokens.
     * @return self
     */
    public function usingMaxTokens(int $maxTokens): self
    {
        $this->modelConfig->setMaxTokens($maxTokens);
        return $this;
    }

    /**
     * Sets the temperature for generation.
     *
     * @since 0.1.0
     *
     * @param float $temperature The temperature value.
     * @return self
     */
    public function usingTemperature(float $temperature): self
    {
        $this->modelConfig->setTemperature($temperature);
        return $this;
    }

    /**
     * Sets the top-p value for generation.
     *
     * @since 0.1.0
     *
     * @param float $topP The top-p value.
     * @return self
     */
    public function usingTopP(float $topP): self
    {
        $this->modelConfig->setTopP($topP);
        return $this;
    }

    /**
     * Sets the top-k value for generation.
     *
     * @since 0.1.0
     *
     * @param int $topK The top-k value.
     * @return self
     */
    public function usingTopK(int $topK): self
    {
        $this->modelConfig->setTopK($topK);
        return $this;
    }

    /**
     * Sets stop sequences for generation.
     *
     * @since 0.1.0
     *
     * @param string ...$stopSequences The stop sequences.
     * @return self
     */
    public function usingStopSequences(string ...$stopSequences): self
    {
        $this->modelConfig->setCustomOption('stopSequences', $stopSequences);
        return $this;
    }

    /**
     * Sets the number of candidates to generate.
     *
     * @since 0.1.0
     *
     * @param int $candidateCount The number of candidates.
     * @return self
     */
    public function usingCandidateCount(int $candidateCount): self
    {
        $this->modelConfig->setCandidateCount($candidateCount);
        return $this;
    }

    /**
     * Sets the function declarations available to the model.
     *
     * @since 0.1.0
     *
     * @param FunctionDeclaration ...$functionDeclarations The function declarations.
     * @return self
     */
    public function usingFunctionDeclarations(FunctionDeclaration ...$functionDeclarations): self
    {
        $this->modelConfig->setFunctionDeclarations($functionDeclarations);
        return $this;
    }

    /**
     * Sets the presence penalty for generation.
     *
     * @since 0.1.0
     *
     * @param float $presencePenalty The presence penalty value.
     * @return self
     */
    public function usingPresencePenalty(float $presencePenalty): self
    {
        $this->modelConfig->setPresencePenalty($presencePenalty);
        return $this;
    }

    /**
     * Sets the frequency penalty for generation.
     *
     * @since 0.1.0
     *
     * @param float $frequencyPenalty The frequency penalty value.
     * @return self
     */
    public function usingFrequencyPenalty(float $frequencyPenalty): self
    {
        $this->modelConfig->setFrequencyPenalty($frequencyPenalty);
        return $this;
    }

    /**
     * Sets the web search configuration.
     *
     * @since 0.1.0
     *
     * @param WebSearch $webSearch The web search configuration.
     * @return self
     */
    public function usingWebSearch(WebSearch $webSearch): self
    {
        $this->modelConfig->setWebSearch($webSearch);
        return $this;
    }

    /**
     * Sets the top log probabilities configuration.
     *
     * If $topLogprobs is null, enables log probabilities.
     * If $topLogprobs has a value, enables log probabilities and sets the number of top log probabilities to return.
     *
     * @since 0.1.0
     *
     * @param int|null $topLogprobs The number of top log probabilities to return, or null to enable log probabilities.
     * @return self
     */
    public function usingTopLogprobs(?int $topLogprobs = null): self
    {
        // Always enable log probabilities
        $this->modelConfig->setLogprobs(true);

        // If a specific number is provided, set it
        if ($topLogprobs !== null) {
            $this->modelConfig->setTopLogprobs($topLogprobs);
        }

        return $this;
    }

    /**
     * Sets the output MIME type.
     *
     * @since 0.1.0
     *
     * @param string $mimeType The MIME type.
     * @return self
     */
    public function asOutputMimeType(string $mimeType): self
    {
        $this->modelConfig->setOutputMimeType($mimeType);
        return $this;
    }

    /**
     * Sets the output schema.
     *
     * @since 0.1.0
     *
     * @param array<string, mixed> $schema The output schema.
     * @return self
     */
    public function asOutputSchema(array $schema): self
    {
        $this->modelConfig->setOutputSchema($schema);
        return $this;
    }

    /**
     * Sets the output modalities.
     *
     * @since 0.1.0
     *
     * @param ModalityEnum ...$modalities The output modalities.
     * @return self
     */
    public function asOutputModalities(ModalityEnum ...$modalities): self
    {
        $this->modelConfig->setOutputModalities($modalities);
        return $this;
    }

    /**
     * Sets the output file type.
     *
     * @since 0.1.0
     *
     * @param FileTypeEnum $fileType The output file type.
     * @return self
     */
    public function asOutputFileType(FileTypeEnum $fileType): self
    {
        $this->modelConfig->setOutputFileType($fileType);
        return $this;
    }

    /**
     * Configures the prompt for JSON response output.
     *
     * @since 0.1.0
     *
     * @param array<string, mixed>|null $schema Optional JSON schema.
     * @return self
     */
    public function asJsonResponse(?array $schema = null): self
    {
        $this->asOutputMimeType('application/json');
        if ($schema !== null) {
            $this->asOutputSchema($schema);
        }
        return $this;
    }

    /**
     * Gets the inferred model requirements based on prompt features.
     *
     * @since 0.1.0
     *
     * @param CapabilityEnum $capability The capability the model must support.
     * @return ModelRequirements The inferred requirements.
     */
    private function getModelRequirements(CapabilityEnum $capability): ModelRequirements
    {
        $capabilities = [$capability];
        $inputModalities = [];

        // Check if we have chat history (multiple messages)
        if (count($this->messages) > 1) {
            $capabilities[] = CapabilityEnum::chatHistory();
        }

        // Analyze all messages to determine required input modalities
        $hasFunctionMessageParts = false;
        foreach ($this->messages as $message) {
            foreach ($message->getParts() as $part) {
                // Check for text input
                if ($part->getType()->isText()) {
                    $inputModalities[] = ModalityEnum::text();
                }

                // Check for file inputs
                if ($part->getType()->isFile()) {
                    $file = $part->getFile();

                    if ($file !== null) {
                        if ($file->isImage()) {
                            $inputModalities[] = ModalityEnum::image();
                        } elseif ($file->isAudio()) {
                            $inputModalities[] = ModalityEnum::audio();
                        } elseif ($file->isVideo()) {
                            $inputModalities[] = ModalityEnum::video();
                        } elseif ($file->isDocument() || $file->isText()) {
                            $inputModalities[] = ModalityEnum::document();
                        }
                    }
                }

                // Check for function calls/responses (these might require special capabilities)
                if ($part->getType()->isFunctionCall() || $part->getType()->isFunctionResponse()) {
                    $hasFunctionMessageParts = true;
                }
            }
        }

        // Build required options from ModelConfig
        $requiredOptions = $this->modelConfig->toRequiredOptions();

        if ($hasFunctionMessageParts) {
            // Add function declarations option if we have function calls/responses
            $requiredOptions = $this->includeInRequiredOptions(
                $requiredOptions,
                new RequiredOption(OptionEnum::functionDeclarations(), true)
            );
        }

        // Add input modalities if we have any inputs
        $requiredOptions = $this->includeInRequiredOptions(
            $requiredOptions,
            new RequiredOption(OptionEnum::inputModalities(), $inputModalities)
        );

        return new ModelRequirements(
            $capabilities,
            $requiredOptions
        );
    }

    /**
     * Infers the capability from configured output modalities.
     *
     * @since 0.1.0
     *
     * @return CapabilityEnum The inferred capability.
     * @throws RuntimeException If the output modality is not supported.
     */
    private function inferCapabilityFromOutputModalities(): CapabilityEnum
    {
        // Get the configured output modalities
        $outputModalities = $this->modelConfig->getOutputModalities();

        // Default to text if no output modality is specified
        if ($outputModalities === null || empty($outputModalities)) {
            return CapabilityEnum::textGeneration();
        }

        // Multi-modal output (multiple modalities) defaults to text generation. This is temporary
        // as a multi-modal interface will be implemented in the future.
        if (count($outputModalities) > 1) {
            return CapabilityEnum::textGeneration();
        }

        // Infer capability from single output modality
        $outputModality = $outputModalities[0];

        if ($outputModality->isText()) {
            return CapabilityEnum::textGeneration();
        } elseif ($outputModality->isImage()) {
            return CapabilityEnum::imageGeneration();
        } elseif ($outputModality->isAudio()) {
            return CapabilityEnum::speechGeneration();
        } elseif ($outputModality->isVideo()) {
            return CapabilityEnum::videoGeneration();
        } else {
            // For unsupported modalities, provide a clear error message
            throw new RuntimeException(
                sprintf('Output modality "%s" is not yet supported.', $outputModality->value)
            );
        }
    }

    /**
     * Infers the capability from a model's implemented interfaces.
     *
     * @since 0.1.0
     *
     * @param ModelInterface $model The model to infer capability from.
     * @return CapabilityEnum|null The inferred capability, or null if none can be inferred.
     */
    private function inferCapabilityFromModelInterfaces(ModelInterface $model): ?CapabilityEnum
    {
        // Check model interfaces in order of preference
        if ($model instanceof TextGenerationModelInterface) {
            return CapabilityEnum::textGeneration();
        }
        if ($model instanceof ImageGenerationModelInterface) {
            return CapabilityEnum::imageGeneration();
        }
        if ($model instanceof TextToSpeechConversionModelInterface) {
            return CapabilityEnum::textToSpeechConversion();
        }
        if ($model instanceof SpeechGenerationModelInterface) {
            return CapabilityEnum::speechGeneration();
        }

        // No supported interface found
        return null;
    }

    /**
     * Checks if the current prompt is supported by the selected model.
     *
     * @since 0.1.0
     *
     * @param CapabilityEnum|null $intendedCapability Optional capability to check support for.
     * @return bool True if supported, false otherwise.
     */
    private function isSupported(?CapabilityEnum $intendedCapability = null): bool
    {
        // If no intended capability provided, infer from output modalities
        if ($intendedCapability === null) {
            $intendedCapability = $this->inferCapabilityFromOutputModalities();
        }

        // Build requirements with the specified capability
        $requirements = $this->getModelRequirements($intendedCapability);

        // If the model has been set, check if it meets the requirements
        if ($this->model !== null) {
            return $this->model->metadata()->meetsRequirements($requirements);
        }

        try {
            // Check if any models support these requirements
            $models = $this->registry->findModelsMetadataForSupport($requirements);
            return !empty($models);
        } catch (InvalidArgumentException $e) {
            // No models support the requirements
            return false;
        }
    }

    /**
     * Checks if the prompt is supported for text generation.
     *
     * @since 0.1.0
     *
     * @return bool True if text generation is supported.
     */
    public function isSupportedForTextGeneration(): bool
    {
        return $this->isSupported(CapabilityEnum::textGeneration());
    }

    /**
     * Checks if the prompt is supported for image generation.
     *
     * @since 0.1.0
     *
     * @return bool True if image generation is supported.
     */
    public function isSupportedForImageGeneration(): bool
    {
        return $this->isSupported(CapabilityEnum::imageGeneration());
    }

    /**
     * Checks if the prompt is supported for text to speech conversion.
     *
     * @since 0.1.0
     *
     * @return bool True if text to speech conversion is supported.
     */
    public function isSupportedForTextToSpeechConversion(): bool
    {
        return $this->isSupported(CapabilityEnum::textToSpeechConversion());
    }

    /**
     * Checks if the prompt is supported for video generation.
     *
     * @since 0.1.0
     *
     * @return bool True if video generation is supported.
     */
    public function isSupportedForVideoGeneration(): bool
    {
        return $this->isSupported(CapabilityEnum::videoGeneration());
    }

    /**
     * Checks if the prompt is supported for speech generation.
     *
     * @since 0.1.0
     *
     * @return bool True if speech generation is supported.
     */
    public function isSupportedForSpeechGeneration(): bool
    {
        return $this->isSupported(CapabilityEnum::speechGeneration());
    }

    /**
     * Checks if the prompt is supported for music generation.
     *
     * @since 0.1.0
     *
     * @return bool True if music generation is supported.
     */
    public function isSupportedForMusicGeneration(): bool
    {
        return $this->isSupported(CapabilityEnum::musicGeneration());
    }

    /**
     * Checks if the prompt is supported for embedding generation.
     *
     * @since 0.1.0
     *
     * @return bool True if embedding generation is supported.
     */
    public function isSupportedForEmbeddingGeneration(): bool
    {
        return $this->isSupported(CapabilityEnum::embeddingGeneration());
    }

    /**
     * Generates a result from the prompt.
     *
     * This is the primary execution method that generates a result (containing
     * potentially multiple candidates) based on the specified capability or
     * the configured output modality.
     *
     * @since 0.1.0
     *
     * @param CapabilityEnum|null $capability Optional capability to use for generation.
     *                                        If null, capability is inferred from output modality.
     * @return GenerativeAiResult The generated result containing candidates.
     * @throws InvalidArgumentException If the prompt or model validation fails.
     * @throws RuntimeException If the model doesn't support the required capability.
     */
    public function generateResult(?CapabilityEnum $capability = null): GenerativeAiResult
    {
        $this->validateMessages();

        // If capability is not provided, infer it
        if ($capability === null) {
            // First try to infer from a specific model if one is set
            if ($this->model !== null) {
                $inferredCapability = $this->inferCapabilityFromModelInterfaces($this->model);
                if ($inferredCapability !== null) {
                    $capability = $inferredCapability;
                }
            }

            // If still no capability, infer from output modalities
            if ($capability === null) {
                $capability = $this->inferCapabilityFromOutputModalities();
            }
        }

        $model = $this->getConfiguredModel($capability);

        // Route to the appropriate generation method based on capability
        if ($capability->isTextGeneration()) {
            if (!$model instanceof TextGenerationModelInterface) {
                throw new RuntimeException(
                    sprintf(
                        'Model "%s" does not support text generation.',
                        $model->metadata()->getId()
                    )
                );
            }
            return $model->generateTextResult($this->messages);
        }

        if ($capability->isImageGeneration()) {
            if (!$model instanceof ImageGenerationModelInterface) {
                throw new RuntimeException(
                    sprintf(
                        'Model "%s" does not support image generation.',
                        $model->metadata()->getId()
                    )
                );
            }
            return $model->generateImageResult($this->messages);
        }

        if ($capability->isTextToSpeechConversion()) {
            if (!$model instanceof TextToSpeechConversionModelInterface) {
                throw new RuntimeException(
                    sprintf(
                        'Model "%s" does not support text-to-speech conversion.',
                        $model->metadata()->getId()
                    )
                );
            }
            return $model->convertTextToSpeechResult($this->messages);
        }

        if ($capability->isSpeechGeneration()) {
            if (!$model instanceof SpeechGenerationModelInterface) {
                throw new RuntimeException(
                    sprintf(
                        'Model "%s" does not support speech generation.',
                        $model->metadata()->getId()
                    )
                );
            }
            return $model->generateSpeechResult($this->messages);
        }

        if ($capability->isVideoGeneration()) {
            // Video generation is not yet implemented
            throw new RuntimeException('Output modality "video" is not yet supported.');
        }

        // TODO: Add support for other capabilities when interfaces are available
        throw new RuntimeException(
            sprintf('Capability "%s" is not yet supported for generation.', $capability->value)
        );
    }

    /**
     * Generates a text result from the prompt.
     *
     * @since 0.1.0
     *
     * @return GenerativeAiResult The generated result containing text candidates.
     * @throws InvalidArgumentException If the prompt or model validation fails.
     * @throws RuntimeException If the model doesn't support text generation.
     */
    public function generateTextResult(): GenerativeAiResult
    {
        // Include text in output modalities
        $this->includeOutputModalities(ModalityEnum::text());

        // Generate and return the result with text generation capability
        return $this->generateResult(CapabilityEnum::textGeneration());
    }

    /**
     * Generates an image result from the prompt.
     *
     * @since 0.1.0
     *
     * @return GenerativeAiResult The generated result containing image candidates.
     * @throws InvalidArgumentException If the prompt or model validation fails.
     * @throws RuntimeException If the model doesn't support image generation.
     */
    public function generateImageResult(): GenerativeAiResult
    {
        // Include image in output modalities
        $this->includeOutputModalities(ModalityEnum::image());

        // Generate and return the result with image generation capability
        return $this->generateResult(CapabilityEnum::imageGeneration());
    }

    /**
     * Generates a speech result from the prompt.
     *
     * @since 0.1.0
     *
     * @return GenerativeAiResult The generated result containing speech audio candidates.
     * @throws InvalidArgumentException If the prompt or model validation fails.
     * @throws RuntimeException If the model doesn't support speech generation.
     */
    public function generateSpeechResult(): GenerativeAiResult
    {
        // Include audio in output modalities
        $this->includeOutputModalities(ModalityEnum::audio());

        // Generate and return the result with speech generation capability
        return $this->generateResult(CapabilityEnum::speechGeneration());
    }

    /**
     * Converts text to speech and returns the result.
     *
     * @since 0.1.0
     *
     * @return GenerativeAiResult The generated result containing speech audio candidates.
     * @throws InvalidArgumentException If the prompt or model validation fails.
     * @throws RuntimeException If the model doesn't support text-to-speech conversion.
     */
    public function convertTextToSpeechResult(): GenerativeAiResult
    {
        // Include audio in output modalities
        $this->includeOutputModalities(ModalityEnum::audio());

        // Generate and return the result with text-to-speech conversion capability
        return $this->generateResult(CapabilityEnum::textToSpeechConversion());
    }

    /**
     * Generates text from the prompt.
     *
     * @since 0.1.0
     *
     * @return string The generated text.
     * @throws InvalidArgumentException If the prompt or model validation fails.
     */
    public function generateText(): string
    {
        return $this->generateTextResult()->toText();
    }

    /**
     * Generates multiple text candidates from the prompt.
     *
     * @since 0.1.0
     *
     * @param int|null $candidateCount The number of candidates to generate.
     * @return list<string> The generated texts.
     * @throws InvalidArgumentException If the prompt or model validation fails.
     */
    public function generateTexts(?int $candidateCount = null): array
    {
        if ($candidateCount !== null) {
            $this->usingCandidateCount($candidateCount);
        }

        // Generate text result
        return $this->generateTextResult()->toTexts();
    }

    /**
     * Generates an image from the prompt.
     *
     * @since 0.1.0
     *
     * @return File The generated image file.
     * @throws InvalidArgumentException If the prompt or model validation fails.
     * @throws RuntimeException If no image is generated.
     */
    public function generateImage(): File
    {
        return $this->generateImageResult()->toFile();
    }

    /**
     * Generates multiple images from the prompt.
     *
     * @since 0.1.0
     *
     * @param int|null $candidateCount The number of images to generate.
     * @return list<File> The generated image files.
     * @throws InvalidArgumentException If the prompt or model validation fails.
     * @throws RuntimeException If no images are generated.
     */
    public function generateImages(?int $candidateCount = null): array
    {
        if ($candidateCount !== null) {
            $this->usingCandidateCount($candidateCount);
        }

        return $this->generateImageResult()->toFiles();
    }

    /**
     * Converts text to speech.
     *
     * @since 0.1.0
     *
     * @return File The generated speech audio file.
     * @throws InvalidArgumentException If the prompt or model validation fails.
     * @throws RuntimeException If no audio is generated.
     */
    public function convertTextToSpeech(): File
    {
        return $this->convertTextToSpeechResult()->toFile();
    }

    /**
     * Converts text to multiple speech outputs.
     *
     * @since 0.1.0
     *
     * @param int|null $candidateCount The number of speech outputs to generate.
     * @return list<File> The generated speech audio files.
     * @throws InvalidArgumentException If the prompt or model validation fails.
     * @throws RuntimeException If no audio is generated.
     */
    public function convertTextToSpeeches(?int $candidateCount = null): array
    {
        if ($candidateCount !== null) {
            $this->usingCandidateCount($candidateCount);
        }

        return $this->convertTextToSpeechResult()->toFiles();
    }

    /**
     * Generates speech from the prompt.
     *
     * @since 0.1.0
     *
     * @return File The generated speech audio file.
     * @throws InvalidArgumentException If the prompt or model validation fails.
     * @throws RuntimeException If no audio is generated.
     */
    public function generateSpeech(): File
    {
        return $this->generateSpeechResult()->toFile();
    }

    /**
     * Generates multiple speech outputs from the prompt.
     *
     * @since 0.1.0
     *
     * @param int|null $candidateCount The number of speech outputs to generate.
     * @return list<File> The generated speech audio files.
     * @throws InvalidArgumentException If the prompt or model validation fails.
     * @throws RuntimeException If no audio is generated.
     */
    public function generateSpeeches(?int $candidateCount = null): array
    {
        if ($candidateCount !== null) {
            $this->usingCandidateCount($candidateCount);
        }

        return $this->generateSpeechResult()->toFiles();
    }

    /**
     * Appends a MessagePart to the messages array.
     *
     * If the last message has a user role, the part is added to it.
     * Otherwise, a new UserMessage is created with the part.
     *
     * @since 0.1.0
     *
     * @param MessagePart $part The part to append.
     * @return void
     */
    protected function appendPartToMessages(MessagePart $part): void
    {
        $lastMessage = end($this->messages);

        if ($lastMessage instanceof Message && $lastMessage->getRole()->isUser()) {
            // Replace the last message with a new one containing the appended part
            array_pop($this->messages);
            $this->messages[] = $lastMessage->withPart($part);
            return;
        }

        // Create new UserMessage with the part
        $this->messages[] = new UserMessage([$part]);
    }

    /**
     * Gets the model to use for generation.
     *
     * If a model has been explicitly set, validates it meets requirements and returns it.
     * Otherwise, finds a suitable model based on the prompt requirements.
     *
     * @since 0.1.0
     *
     * @param CapabilityEnum $capability The capability the model will be using.
     * @return ModelInterface The model to use.
     * @throws InvalidArgumentException If no suitable model is found or set model doesn't meet requirements.
     */
    private function getConfiguredModel(CapabilityEnum $capability): ModelInterface
    {
        $requirements = $this->getModelRequirements($capability);

        // If a model has been explicitly set, return it
        if ($this->model !== null) {
            $this->model->setConfig($this->modelConfig);
            $this->registry->bindModelDependencies($this->model);
            return $this->model;
        }

        // Find a suitable model based on requirements
        if ($this->providerIdOrClassName === null) {
            $providerModelsMetadata = $this->registry->findModelsMetadataForSupport($requirements);

            if (empty($providerModelsMetadata)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'No models found that support the required capabilities and options for this prompt. ' .
                        'Required capabilities: %s. Required options: %s',
                        implode(', ', array_map(function ($cap) {
                            return $cap->value;
                        }, $requirements->getRequiredCapabilities())),
                        implode(', ', array_map(function ($opt) {
                            return $opt->getName()->value . '=' . json_encode($opt->getValue());
                        }, $requirements->getRequiredOptions()))
                    )
                );
            }

            $firstProviderModels = $providerModelsMetadata[0];
            $provider = $firstProviderModels->getProvider()->getId();
            $modelMetadata = $firstProviderModels->getModels()[0];
        } else {
            $modelsMetadata = $this->registry->findProviderModelsMetadataForSupport(
                $this->providerIdOrClassName,
                $requirements
            );

            if (empty($modelsMetadata)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'No models found for %s that support the required capabilities and options for this prompt. ' .
                        'Required capabilities: %s. Required options: %s',
                        $this->providerIdOrClassName,
                        implode(', ', array_map(function ($cap) {
                            return $cap->value;
                        }, $requirements->getRequiredCapabilities())),
                        implode(', ', array_map(function ($opt) {
                            return $opt->getName()->value . '=' . json_encode($opt->getValue());
                        }, $requirements->getRequiredOptions()))
                    )
                );
            }

            $provider = $this->providerIdOrClassName;
            $modelMetadata = $modelsMetadata[0];
        }

        // Get the model instance from the provider
        return $this->registry->getProviderModel(
            $provider,
            $modelMetadata->getId(),
            $this->modelConfig
        );
    }

    /**
     * Parses various input types into a Message with the given role.
     *
     * @since 0.1.0
     *
     * @param mixed $input The input to parse.
     * @param MessageRoleEnum $defaultRole The role for the message if not specified by input.
     * @return Message The parsed message.
     * @throws InvalidArgumentException If the input type is not supported or results in empty message.
     */
    private function parseMessage($input, MessageRoleEnum $defaultRole): Message
    {
        // Handle Message input directly
        if ($input instanceof Message) {
            return $input;
        }

        // Handle single MessagePart
        if ($input instanceof MessagePart) {
            return new Message($defaultRole, [$input]);
        }

        // Handle string input
        if (is_string($input)) {
            if (trim($input) === '') {
                throw new InvalidArgumentException('Cannot create a message from an empty string.');
            }
            return new Message($defaultRole, [new MessagePart($input)]);
        }

        // Handle array input
        if (!is_array($input)) {
            throw new InvalidArgumentException(
                'Input must be a string, MessagePart, MessagePartArrayShape, ' .
                'a list of string|MessagePart|MessagePartArrayShape, or a Message instance.'
            );
        }

        // Handle MessageArrayShape input
        if (Message::isArrayShape($input)) {
            return Message::fromArray($input);
        }

        // Check if it's a MessagePartArrayShape
        if (MessagePart::isArrayShape($input)) {
            return new Message($defaultRole, [MessagePart::fromArray($input)]);
        }

        // It should be a list of string|MessagePart|MessagePartArrayShape
        if (!array_is_list($input)) {
            throw new InvalidArgumentException('Array input must be a list array.');
        }

        // Empty array check
        if (empty($input)) {
            throw new InvalidArgumentException('Cannot create a message from an empty array.');
        }

        $parts = [];
        foreach ($input as $item) {
            if (is_string($item)) {
                $parts[] = new MessagePart($item);
            } elseif ($item instanceof MessagePart) {
                $parts[] = $item;
            } elseif (is_array($item) && MessagePart::isArrayShape($item)) {
                $parts[] = MessagePart::fromArray($item);
            } else {
                throw new InvalidArgumentException(
                    'Array items must be strings, MessagePart instances, or MessagePartArrayShape.'
                );
            }
        }

        return new Message($defaultRole, $parts);
    }

    /**
     * Validates the messages array for prompt generation.
     *
     * Ensures that:
     * - The first message is a user message
     * - The last message is a user message
     * - The last message has parts
     *
     * @since 0.1.0
     *
     * @return void
     * @throws InvalidArgumentException If validation fails.
     */
    private function validateMessages(): void
    {
        if (empty($this->messages)) {
            throw new InvalidArgumentException(
                'Cannot generate from an empty prompt. Add content using withText() or similar methods.'
            );
        }

        $firstMessage = reset($this->messages);
        if (!$firstMessage->getRole()->isUser()) {
            throw new InvalidArgumentException(
                'The first message must be from a user role, not from ' . $firstMessage->getRole()->value
            );
        }

        $lastMessage = end($this->messages);
        if (!$lastMessage->getRole()->isUser()) {
            throw new InvalidArgumentException(
                'The last message must be from a user role, not from ' . $lastMessage->getRole()->value
            );
        }

        if (empty($lastMessage->getParts())) {
            throw new InvalidArgumentException(
                'The last message must have content parts. Add content using withText() or similar methods.'
            );
        }
    }

    /**
     * Checks if the value is a list of Message objects.
     *
     * @since 0.1.0
     *
     * @param mixed $value The value to check.
     * @return bool True if the value is a list of Message objects.
     *
     * @phpstan-assert-if-true list<Message> $value
     */
    private function isMessagesList($value): bool
    {
        if (!is_array($value) || empty($value) || !array_is_list($value)) {
            return false;
        }

        // Check if all items are Messages
        foreach ($value as $item) {
            if (!($item instanceof Message)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Includes a required option in the list if not already present.
     *
     * Checks if a RequiredOption with the same name already exists in the list.
     * If not, adds the new option. Returns the updated list.
     *
     * @since 0.1.0
     *
     * @param list<RequiredOption> $options The existing list of required options.
     * @param RequiredOption $option The option to potentially add.
     * @return list<RequiredOption> The updated list of required options.
     */
    private function includeInRequiredOptions(array $options, RequiredOption $option): array
    {
        // Check if an option with the same name already exists
        foreach ($options as $existingOption) {
            if ($existingOption->getName()->equals($option->getName())) {
                // Option already exists, return unchanged list
                return $options;
            }
        }

        // Add the new option
        $options[] = $option;
        return $options;
    }

    /**
     * Includes output modalities if not already present.
     *
     * Adds the given modalities to the output modalities list if they're not
     * already included. If output modalities is null, initializes it with
     * the given modalities.
     *
     * @since 0.1.0
     *
     * @param ModalityEnum ...$modalities The modalities to include.
     * @return void
     */
    private function includeOutputModalities(ModalityEnum ...$modalities): void
    {
        $existing = $this->modelConfig->getOutputModalities();

        // Initialize if null
        if ($existing === null) {
            $this->modelConfig->setOutputModalities($modalities);
            return;
        }

        // Build a set of existing modality values for O(1) lookup
        $existingValues = [];
        foreach ($existing as $existingModality) {
            $existingValues[$existingModality->value] = true;
        }

        // Add new modalities that don't exist
        $toAdd = [];
        foreach ($modalities as $modality) {
            if (!isset($existingValues[$modality->value])) {
                $toAdd[] = $modality;
            }
        }

        // Update if we have new modalities to add
        if (!empty($toAdd)) {
            $this->modelConfig->setOutputModalities(array_merge($existing, $toAdd));
        }
    }
}
