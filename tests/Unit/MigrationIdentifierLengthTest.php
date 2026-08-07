<?php

it('uses MySQL-safe names for migration identifiers that would reach the limit', function (string $migration, string $generatedName, string $explicitName) {
    $contents = file_get_contents(database_path('migrations/'.$migration));

    expect($contents)
        ->not->toContain($generatedName)
        ->toContain($explicitName)
        ->and(strlen($explicitName))->toBeLessThan(64);
})->with([
    'navigation item ordering index' => [
        '2026_07_29_000000_create_public_chrome_tables.php',
        'navigation_items_navigation_menu_id_parent_id_display_order_index',
        'nav_items_menu_parent_order_idx',
    ],
    'work process deliverable ordering index' => [
        '2026_08_07_020000_create_capabilities_and_work_processes_tables.php',
        'work_process_deliverables_work_process_stage_id_display_order_index',
        'wp_deliverables_stage_order_idx',
    ],
    'work process solution relation unique constraint' => [
        '2026_08_07_020000_create_capabilities_and_work_processes_tables.php',
        'work_process_solution_relations_work_process_id_solution_id_unique',
        'wp_solution_relations_pair_uq',
    ],
    'work process product relation unique constraint' => [
        '2026_08_07_020000_create_capabilities_and_work_processes_tables.php',
        'work_process_product_relations_work_process_id_product_id_unique',
        'wp_product_relations_pair_uq',
    ],
    'work process industry relation unique constraint' => [
        '2026_08_07_020000_create_capabilities_and_work_processes_tables.php',
        'work_process_industry_relations_work_process_id_industry_id_unique',
        'wp_industry_relations_pair_uq',
    ],
    'case study capability relation unique constraint' => [
        '2026_08_07_040000_create_phase_ten_content_tables.php',
        'case_study_capability_relations_case_study_id_capability_id_unique',
        'case_study_capability_pair_uq',
    ],
]);
