<?php

if (! function_exists('tour_table_actions')) {
    /**
     * Langkah page tour untuk tombol tambah dan aksi baris pertama (edit, detail opsional, hapus).
     *
     * @return array<int, array<string, mixed>>
     */
    function tour_table_actions(
        string $prefix,
        string $addTitle,
        string $addDesc,
        string $editTitle,
        string $editDesc,
        string $deleteTitle,
        string $deleteDesc,
        ?string $detailTitle = null,
        ?string $detailDesc = null,
    ): array {
        $steps = [
            [
                'element' => "[data-tour=\"{$prefix}-add-btn\"]",
                'title' => $addTitle,
                'description' => $addDesc,
                'side' => 'left',
            ],
            [
                'element' => "[data-tour=\"{$prefix}-action-edit\"]",
                'title' => $editTitle,
                'description' => $editDesc,
                'side' => 'left',
            ],
        ];

        if ($detailTitle !== null) {
            $steps[] = [
                'element' => "[data-tour=\"{$prefix}-action-detail\"]",
                'title' => $detailTitle,
                'description' => $detailDesc ?? '',
                'side' => 'left',
            ];
        }

        $steps[] = [
            'element' => "[data-tour=\"{$prefix}-action-delete\"]",
            'title' => $deleteTitle,
            'description' => $deleteDesc,
            'side' => 'left',
        ];

        return $steps;
    }
}

if (! function_exists('tour_modal_create_sections')) {
    /**
     * Beberapa langkah sub-tour modal create (openModal => create).
     *
     * @param  array<int, array{element: string, title: string, description: string, side?: string}>  $sections
     * @return array<int, array<string, mixed>>
     */
    function tour_modal_create_sections(array $sections): array
    {
        return array_map(function (array $section) {
            $step = [
                'element' => $section['element'],
                'openModal' => 'create',
                'title' => $section['title'],
                'description' => $section['description'],
                'side' => $section['side'] ?? 'left',
            ];

            if (isset($section['advanceWhen'])) {
                $step['advanceWhen'] = $section['advanceWhen'];
            }

            return $step;
        }, $sections);
    }
}

if (! function_exists('tour_modal_pencapaian_create_sections')) {
    /**
     * Tour modal create pencapaian: isi form per langkah sebelum lanjut.
     *
     * @return array<int, array<string, mixed>>
     */
    function tour_modal_pencapaian_create_sections(bool $includeKelas = true): array
    {
        $sections = [];

        if ($includeKelas) {
            $sections[] = [
                'element' => '[data-tour="modal-create-section-kelas"]',
                'title' => 'Pilih Kelas',
                'description' => 'Filter daftar siswa berdasarkan kelas. Pilih kelas terlebih dahulu  tour akan lanjut otomatis setelah Anda memilih.',
                'advanceWhen' => 'section-input',
            ];
        }

        $sections = array_merge($sections, [
            [
                'element' => '[data-tour="modal-create-section-siswa"]',
                'title' => 'Target Siswa',
                'description' => 'Pilih siswa yang akan dievaluasi. Tour lanjut otomatis setelah Anda memilih.',
                'advanceWhen' => 'section-input',
            ],
            [
                'element' => '[data-tour="modal-create-section-kegiatan"]',
                'title' => 'Pilih Kegiatan',
                'description' => 'Pilih jurnal kegiatan yang sudah dilaksanakan, terdokumentasi, dan siswa hadir. Tour lanjut setelah Anda memilih.',
                'advanceWhen' => 'section-input',
            ],
            [
                'element' => '[data-tour="modal-create-section-skala"]',
                'title' => 'Pilih Skala Pencapaian',
                'description' => 'Tentukan tingkat capaian siswa untuk indikator matrikulasi pertama. Tour lanjut setelah skala dipilih.',
                'advanceWhen' => 'section-input',
            ],
            [
                'element' => '[data-tour="modal-create-section-feedback"]',
                'title' => 'Berikan Umpan Balik',
                'description' => 'Tulis catatan evaluasi atau gunakan tombol Saran AI. Tour lanjut setelah umpan balik terisi.',
                'advanceWhen' => 'section-input',
            ],
            [
                'element' => '[data-tour="modal-create-section-evidence"]',
                'title' => 'Unggah Dokumentasi (Evidence)',
                'description' => 'Lampirkan foto bukti kegiatan jika perlu. Langkah ini opsional  tekan Lanjut untuk melanjutkan.',
                'advanceWhen' => 'section-optional',
            ],
            [
                'element' => '[data-tour="modal-create-submit"]',
                'title' => 'Simpan',
                'description' => 'Simpan evaluasi pencapaian siswa.',
                'side' => 'top',
            ],
        ]);

        return tour_modal_create_sections($sections);
    }
}

if (! function_exists('tour_modal_detail')) {
    /**
     * Langkah sub-tour modal detail (openModal => detail).
     *
     * @return array<int, array<string, mixed>>
     */
    function tour_modal_detail(
        string $title,
        string $description,
        string $element = '[data-tour="modal-detail"]',
    ): array {
        return [
            [
                'element' => $element,
                'openModal' => 'detail',
                'title' => $title,
                'description' => $description,
                'side' => 'left',
            ],
        ];
    }
}

if (! function_exists('tour_modal_history')) {
    /**
     * Beberapa langkah sub-tour modal riwayat (openModal => history).
     *
     * @param  array<int, array{element: string, title: string, description: string, side?: string}>  $steps
     * @return array<int, array<string, mixed>>
     */
    function tour_modal_history(array $steps): array
    {
        return array_map(function (array $step) {
            return [
                'element' => $step['element'],
                'openModal' => 'history',
                'title' => $step['title'],
                'description' => $step['description'],
                'side' => $step['side'] ?? 'left',
            ];
        }, $steps);
    }
}

if (! function_exists('tour_modal_edit_sections')) {
    /**
     * Beberapa langkah sub-tour modal edit (openModal => edit).
     *
     * @param  array<int, array{element: string, title: string, description: string, side?: string}>  $sections
     * @return array<int, array<string, mixed>>
     */
    function tour_modal_edit_sections(array $sections): array
    {
        return array_map(function (array $section) {
            return [
                'element' => $section['element'],
                'openModal' => 'edit',
                'title' => $section['title'],
                'description' => $section['description'],
                'side' => $section['side'] ?? 'left',
            ];
        }, $sections);
    }
}

if (! function_exists('tour_modal_delete_only')) {
    /**
     * Langkah sub-tour modal hapus saja.
     *
     * @return array<int, array<string, mixed>>
     */
    function tour_modal_delete_only(string $title, string $description): array
    {
        return [
            [
                'element' => '[data-tour="modal-delete"]',
                'openModal' => 'delete',
                'title' => $title,
                'description' => $description,
                'side' => 'left',
            ],
        ];
    }
}

if (! function_exists('tour_mirror_section_element')) {
    function tour_mirror_section_element(string $element): string
    {
        return str_replace(
            ['modal-create-section', 'modal-create-submit'],
            ['modal-edit-section', 'modal-edit-submit'],
            $element,
        );
    }
}

if (! function_exists('tour_modal_edit_sections_mirror_create')) {
    /**
     * Salin section create menjadi section edit dengan selector paralel.
     *
     * @param  array<int, array{element: string, title: string, description: string, side?: string}>  $createSections
     * @param  array<int, array{title?: string, description?: string}>  $overrides
     * @return array<int, array<string, mixed>>
     */
    function tour_modal_edit_sections_mirror_create(array $createSections, array $overrides = []): array
    {
        $editSections = [];

        foreach ($createSections as $index => $section) {
            $isSubmit = str_contains($section['element'], 'modal-create-submit');
            $defaultTitle = $isSubmit
                ? 'Simpan'
                : preg_replace('/^(Isi|Buat|Tambah|Daftarkan|Catat|Publikasikan|Kirim|Nilai)\s+/u', 'Ubah ', $section['title']);
            $defaultDescription = $isSubmit
                ? 'Kirim perubahan setelah semua data benar.'
                : $section['description'];

            $editSections[] = [
                'element' => tour_mirror_section_element($section['element']),
                'title' => $overrides[$index]['title'] ?? $defaultTitle,
                'description' => $overrides[$index]['description'] ?? $defaultDescription,
                'side' => $overrides[$index]['side'] ?? ($section['side'] ?? 'left'),
            ];
        }

        return tour_modal_edit_sections($editSections);
    }
}

if (! function_exists('tour_crud_modal_bundle')) {
    /**
     * Bundle create + edit (mirror) + delete untuk hub CRUD standar.
     *
     * @param  array<int, array{element: string, title: string, description: string, side?: string}>  $createSections
     * @param  array<int, array{title?: string, description?: string, side?: string}>  $editOverrides
     * @param  array<int, array<string, mixed>>  $extra
     * @return array<int, array<string, mixed>>
     */
    function tour_crud_modal_bundle(
        array $createSections,
        string $deleteTitle,
        string $deleteDesc,
        array $editOverrides = [],
        bool $includeDelete = true,
        array $extra = [],
    ): array {
        $bundle = array_merge(
            tour_modal_create_sections($createSections),
            tour_modal_edit_sections_mirror_create($createSections, $editOverrides),
        );

        if ($includeDelete) {
            $bundle = array_merge($bundle, tour_modal_delete_only($deleteTitle, $deleteDesc));
        }

        return array_merge($bundle, $extra);
    }
}

if (! function_exists('tour_modal_edit_delete')) {
    /**
     * Langkah sub-tour modal edit dan hapus.
     *
     * @return array<int, array<string, mixed>>
     */
    function tour_modal_edit_delete(
        string $editTitle,
        string $editDesc,
        string $deleteTitle,
        string $deleteDesc,
        bool $includeDelete = true,
    ): array {
        $steps = [
            [
                'element' => '[data-tour="modal-edit"]',
                'openModal' => 'edit',
                'title' => $editTitle,
                'description' => $editDesc,
                'side' => 'left',
            ],
        ];

        if ($includeDelete) {
            $steps[] = [
                'element' => '[data-tour="modal-delete"]',
                'openModal' => 'delete',
                'title' => $deleteTitle,
                'description' => $deleteDesc,
                'side' => 'left',
            ];
        }

        return $steps;
    }
}

if (! function_exists('tour_crud_modals')) {
    /**
     * Langkah tour standar untuk modal create, edit, dan hapus.
     *
     * @return array<int, array<string, mixed>>
     */
    function tour_crud_modals(
        string $createTitle,
        string $createDesc,
        string $editTitle,
        string $editDesc,
        string $deleteTitle,
        string $deleteDesc,
        bool $includeDelete = true,
    ): array {
        return array_merge(
            tour_modal_create_sections([
                [
                    'element' => '[data-tour="modal-create"]',
                    'title' => $createTitle,
                    'description' => $createDesc,
                ],
            ]),
            tour_modal_edit_delete($editTitle, $editDesc, $deleteTitle, $deleteDesc, $includeDelete),
        );
    }
}
