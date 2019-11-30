<?php

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setRules(
        [
            'array_syntax' => ['syntax' => 'short'],
            'binary_operator_spaces' => [
                'align_double_arrow' => false,
                'align_equals' => false,
            ],
            'blank_line_after_namespace' => true,
            'blank_line_after_opening_tag' => true,
            'blank_line_before_return' => true,
            'braces' => true,
            'cast_spaces' => true,
            'class_definition' => true,
            'concat_space' => ['spacing' => 'one'],
            'elseif' => true,
            'encoding' => true,
            'full_opening_tag' => true,
            'function_declaration' => true,
            'function_typehint_space' => true,
            'single_line_comment_style' => false,
            'heredoc_to_nowdoc' => true,
            'include' => true,
            'lowercase_cast' => true,
            'indentation_type' => true,
            'line_ending' => true,
            'lowercase_constants' => true,
            'lowercase_keywords' => true,
            'method_argument_space' => true,
            'method_separation' => true,
            'native_function_casing' => true,
            'new_with_braces' => true,
            'native_function_invocation' => false,
            'no_alias_functions' => true,
            'no_blank_lines_after_class_opening' => true,
            'no_blank_lines_after_phpdoc' => true,
            'no_closing_tag' => true,
            'no_empty_phpdoc' => true,
            'no_empty_statement' => true,
            'no_extra_consecutive_blank_lines' => true,
            'no_leading_import_slash' => true,
            'no_leading_namespace_whitespace' => true,
            'no_multiline_whitespace_around_double_arrow' => true,
            'no_multiline_whitespace_before_semicolons' => true,
            'no_short_bool_cast' => true,
            'no_singleline_whitespace_before_semicolons' => true,
            'no_spaces_after_function_name' => true,
            'no_spaces_inside_parenthesis' => true,
            'no_trailing_comma_in_list_call' => true,
            'no_trailing_comma_in_singleline_array' => true,
            'no_trailing_whitespace' => true,
            'no_trailing_whitespace_in_comment' => true,
            'no_unneeded_control_parentheses' => true,
            'no_unreachable_default_argument_value' => true,
            'no_unused_imports' => true,
            'no_useless_return' => true,
            'no_whitespace_before_comma_in_array' => true,
            'no_whitespace_in_blank_line' => true,
            'not_operator_with_successor_space' => true,
            'object_operator_without_whitespace' => true,
            'ordered_imports' => ['sort_algorithm' => 'alpha'],
            'phpdoc_align' => ["align" => "left"],
            'phpdoc_indent' => true,
            'phpdoc_inline_tag' => true,
            'phpdoc_no_access' => true,
            'phpdoc_no_empty_return' => false,
            'phpdoc_no_package' => false,
            'phpdoc_order' => true,
            'phpdoc_scalar' => true,
            'phpdoc_separation' => true,
            'phpdoc_summary' => true,
            'phpdoc_to_comment' => true,
            'phpdoc_trim' => true,
            'phpdoc_types' => true,
            'phpdoc_var_without_name' => true,
            'psr4' => true,
            'self_accessor' => true,
            'short_scalar_cast' => true,
            'simplified_null_return' => true,
            'single_blank_line_at_eof' => true,
            'single_blank_line_before_namespace' => true,
            'single_import_per_statement' => true,
            'single_line_after_imports' => true,
            'single_quote' => true,
            'space_after_semicolon' => true,
            'standardize_not_equals' => true,
            'switch_case_semicolon_to_colon' => true,
            'switch_case_space' => true,
            'ternary_operator_spaces' => true,
            'trailing_comma_in_multiline_array' => true,
            'trim_array_spaces' => true,
            'unary_operator_spaces' => true,
            'visibility_required' => true,
            'whitespace_after_comma_in_array' => true,
        ]
    )
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->notPath('dist')
            ->notPath('node_modules')
            ->notPath('bootstrap/cache')
            ->notPath('storage')
            ->notPath('vendor')
            ->in(__DIR__)
            ->name('*.php')
            ->notName('*.blade.php')
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
    );
