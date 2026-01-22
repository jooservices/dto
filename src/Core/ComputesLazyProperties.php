<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

use Closure;

/**
 * Interface for DTOs that can compute lazy/derived properties on-demand.
 *
 * Lazy properties are NOT part of the DTO's data structure but are computed
 * when explicitly requested during serialization. This is useful for:
 * - Expensive computations that are not always needed
 * - Derived values calculated from existing properties
 * - Properties that should only be included in specific contexts
 *
 * IMPORTANT RULES:
 *
 * 1. DERIVED DATA ONLY - No IO operations:
 *    ✅ DO: Compute from existing properties (fullName, initials, computed stats)
 *    ❌ DON'T: Database queries, API calls, file I/O, network requests
 *
 * 2. CLOSURE-BASED for true lazy evaluation:
 *    Return closures for expensive computations to defer execution until needed.
 *    Value is only computed if included in serialization.
 *
 * 3. NO COLLISION with property names:
 *    Lazy property names must not match existing DTO properties.
 *    Will throw LogicException if collision is detected.
 *
 * 4. NO TRANSFORMER SUPPORT:
 *    Lazy values don't go through the attribute/transformer pipeline.
 *    Perform any needed transformations inside computeLazyProperties().
 *
 * Example:
 *
 *   class UserDto extends Dto implements ComputesLazyProperties
 *   {
 *       public readonly string $firstName;
 *       public readonly string $lastName;
 *       public readonly string $email;
 *
 *       public function computeLazyProperties(): array
 *       {
 *           return [
 *               // Immediate value (computed when this method is called)
 *               'fullName' => $this->firstName . ' ' . $this->lastName,
 *
 *               // Closure (computed only if requested in serialization)
 *               'stats' => fn() => [
 *                   'nameLength' => strlen($this->firstName . $this->lastName),
 *                   'emailDomain' => explode('@', $this->email)[1] ?? '',
 *               ],
 *           ];
 *       }
 *   }
 *
 *   // Usage:
 *   $user = UserDto::from(['firstName' => 'John', 'lastName' => 'Doe', 'email' => 'john@example.com']);
 *
 *   // Normal serialization - lazy properties excluded
 *   $user->toArray(); // ['firstName' => 'John', 'lastName' => 'Doe', 'email' => '...']
 *
 *   // Include specific lazy properties
 *   $options = (new SerializationOptions())->withIncludeLazy(['fullName']);
 *   $context = new Context(serializationOptions: $options);
 *   $user->toArray($context); // Now includes 'fullName'
 *
 * @see SerializationOptions::withIncludeLazy()
 */
interface ComputesLazyProperties
{
    /**
     * Return lazy property definitions.
     *
     * Keys are property names, values can be:
     * - Direct values: Computed immediately when this method is called
     * - Closures: Deferred until the property is actually needed (truly lazy)
     *
     * @return array<string, Closure|mixed> Map of property name to value or Closure
     */
    public function computeLazyProperties(): array;
}
