<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

// This configuration aligns with Laravel/Pint standards
// Pint is the primary code style enforcer - this config should match Pint's Laravel preset
return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        // Laravel uses PSR-12 as base
        '@PSR12' => true,
        '@PSR12:risky' => true,

        // PHP 8.5+ migration rules
        '@PHP84Migration' => true,

        // Strict typing (Laravel compatible)
        'declare_strict_types' => true,
        'strict_param' => true,
        'strict_comparison' => true,

        // Array notation
        'array_syntax' => ['syntax' => 'short'],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_trailing_comma_in_singleline' => true,
        'no_whitespace_before_comma_in_array' => true,
        'normalize_index_brace' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters', 'match'],
        ],
        'trim_array_spaces' => true,
        'whitespace_after_comma_in_array' => ['ensure_single_space' => true],

        // Braces and control structures (Laravel standard)
        'control_structure_braces' => true,
        'control_structure_continuation_position' => ['position' => 'same_line'],
        'curly_braces_position' => [
            'classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
        ],
        'no_alternative_syntax' => true,
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'simplified_if_return' => false,
        'elseif' => true,

        // Casing
        'class_reference_name_casing' => true,
        'constant_case' => ['case' => 'lower'],
        'integer_literal_case' => true,
        'lowercase_keywords' => true,
        'lowercase_static_reference' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'native_function_casing' => true,
        'native_type_declaration_casing' => true,

        // Cast notation
        'cast_spaces' => ['space' => 'single'],
        'lowercase_cast' => true,
        'modernize_types_casting' => true,
        'no_short_bool_cast' => true,
        'no_unset_cast' => true,
        'short_scalar_cast' => true,

        // Class notation
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],
        'class_definition' => [
            'inline_constructor_arguments' => false,
            'multi_line_extends_each_single_line' => true,
            'single_item_single_line' => true,
            'single_line' => true,
            'space_before_parenthesis' => true,
        ],
        'final_class' => false,
        'final_internal_class' => false,
        'no_blank_lines_after_class_opening' => true,
        'no_null_property_initialization' => true,
        'no_php4_constructor' => true,
        'no_unneeded_final_method' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public_static',
                'property_protected_static',
                'property_private_static',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public_static',
                'method_public_abstract_static',
                'method_protected_static',
                'method_protected_abstract_static',
                'method_private_static',
                'method_public',
                'method_public_abstract',
                'method_protected',
                'method_protected_abstract',
                'method_private',
            ],
        ],
        'ordered_interfaces' => ['order' => 'alpha'],
        'ordered_traits' => true,
        'ordered_types' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'alpha'],
        'protected_to_private' => true,
        'self_accessor' => true,
        'self_static_accessor' => true,
        'single_class_element_per_statement' => ['elements' => ['const', 'property']],
        'visibility_required' => ['elements' => ['property', 'method', 'const']],

        // Comment
        'comment_to_phpdoc' => true,
        'multiline_comment_opening_closing' => true,
        'no_empty_comment' => true,
        'no_trailing_whitespace_in_comment' => true,
        'single_line_comment_spacing' => true,
        'single_line_comment_style' => ['comment_types' => ['hash']],

        // Function notation
        'combine_nested_dirname' => true,
        'fopen_flag_order' => true,
        'fopen_flags' => ['b_mode' => true],
        'function_declaration' => ['closure_function_spacing' => 'one', 'closure_fn_spacing' => 'one'],
        'function_typehint_space' => true,
        'implode_call' => true,
        'lambda_not_used_import' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'attribute_placement' => 'standalone',
        ],
        'no_spaces_after_function_name' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_sprintf' => true,
        'nullable_type_declaration' => ['syntax' => 'question_mark'],
        'nullable_type_declaration_for_default_null_value' => true,
        'phpdoc_to_param_type' => false,
        'phpdoc_to_property_type' => false,
        'phpdoc_to_return_type' => false,
        'return_type_declaration' => ['space_before' => 'none'],
        'single_line_throw' => false,
        'static_lambda' => true,
        'use_arrow_functions' => true,
        'void_return' => true,

        // Import
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'group_import' => false,
        'no_leading_import_slash' => true,
        'no_unneeded_import_alias' => true,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'single_import_per_statement' => true,

        // Language construct
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'declare_equal_normalize' => ['space' => 'none'],
        'declare_parentheses' => true,
        'dir_constant' => true,
        'error_suppression' => false,
        'explicit_indirect_variable' => true,
        'function_to_constant' => true,
        'get_class_to_class_keyword' => true,
        'is_null' => true,
        'no_unset_on_property' => true,
        'nullable_type_declaration' => true,
        'single_space_around_construct' => true,

        // List notation
        'list_syntax' => ['syntax' => 'short'],

        // Namespace notation
        'blank_line_after_namespace' => true,
        'blank_lines_before_namespace' => ['max_line_breaks' => 2, 'min_line_breaks' => 2],
        'clean_namespace' => true,
        'no_blank_lines_before_namespace' => false,
        'no_leading_namespace_whitespace' => true,

        // Naming
        'no_homoglyph_names' => true,

        // Operator (Laravel prefers no spaces around concat)
        'assign_null_coalescing_to_coalesce_equal' => true,
        'binary_operator_spaces' => ['default' => 'single_space'],
        'concat_space' => ['spacing' => 'none'],
        'increment_style' => ['style' => 'pre'],
        'logical_operators' => true,
        'new_with_parentheses' => ['anonymous_class' => true, 'named_class' => true],
        'no_space_around_double_colon' => true,
        'no_useless_concat_operator' => true,
        'no_useless_nullsafe_operator' => true,
        'not_operator_with_space' => false,
        'not_operator_with_successor_space' => false,
        'object_operator_without_whitespace' => true,
        'operator_linebreak' => ['only_booleans' => true, 'position' => 'beginning'],
        'standardize_increment' => true,
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'ternary_to_elvis_operator' => true,
        'ternary_to_null_coalescing' => true,
        'unary_operator_spaces' => true,

        // PHPDoc
        'align_multiline_comment' => ['comment_type' => 'phpdocs_only'],
        'general_phpdoc_annotation_remove' => [
            'annotations' => ['author', 'package', 'subpackage'],
        ],
        'general_phpdoc_tag_rename' => [
            'replacements' => ['inheritDocs' => 'inheritDoc'],
        ],
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'allow_unused_params' => false,
            'remove_inheritdoc' => true,
        ],
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_line_span' => [
            'const' => 'single',
            'method' => 'multi',
            'property' => 'single',
        ],
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => ['replacements' => ['type' => 'var']],
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_order' => ['order' => ['param', 'return', 'throws']],
        'phpdoc_order_by_value' => [
            'annotations' => ['covers', 'dataProvider', 'depends', 'group', 'requires', 'throws', 'uses'],
        ],
        'phpdoc_return_self_reference' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_tag_casing' => true,
        'phpdoc_tag_type' => ['tags' => ['inheritDoc' => 'inline']],
        'phpdoc_to_comment' => false,
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'alpha'],
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => true,

        // Return notation
        'no_useless_return' => true,
        'return_assignment' => true,
        'simplified_null_return' => false,

        // Semicolon
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'no_empty_statement' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'semicolon_after_instruction' => true,
        'space_after_semicolon' => ['remove_in_empty_for_expressions' => true],

        // String notation
        'explicit_string_variable' => true,
        'heredoc_to_nowdoc' => true,
        'no_binary_string' => true,
        'no_trailing_whitespace_in_string' => false,
        'simple_to_complex_string_variable' => true,
        'single_quote' => ['strings_containing_single_quote_chars' => false],
        'string_implicit_backslashes' => ['single_quoted' => 'ignore'],

        // Whitespace
        'array_indentation' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'break',
                'case',
                'continue',
                'declare',
                'default',
                'do',
                'exit',
                'for',
                'foreach',
                'goto',
                'if',
                'include',
                'include_once',
                'phpdoc',
                'require',
                'require_once',
                'return',
                'switch',
                'throw',
                'try',
                'while',
                'yield',
                'yield_from',
            ],
        ],
        'blank_line_between_import_groups' => true,
        'compact_nullable_type_declaration' => true,
        'heredoc_indentation' => ['indentation' => 'same_as_start'],
        'indentation_type' => true,
        'line_ending' => true,
        'method_chaining_indentation' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'attribute',
                'break',
                'case',
                'continue',
                'curly_brace_block',
                'default',
                'extra',
                'parenthesis_brace_block',
                'return',
                'square_brace_block',
                'switch',
                'throw',
                'use',
            ],
        ],
        'no_spaces_around_offset' => ['positions' => ['inside', 'outside']],
        'no_trailing_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'single_blank_line_at_eof' => true,
        'spaces_inside_parentheses' => ['space' => 'none'],
        'statement_indentation' => true,
        'types_spaces' => ['space' => 'none'],
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');
