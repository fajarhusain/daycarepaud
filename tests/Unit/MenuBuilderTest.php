<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\MenuBuilder;
use PHPUnit\Framework\TestCase;

class MenuBuilderTest extends TestCase
{
    public function test_collapsible_count_and_badge_flag_after_permission_filter(): void
    {
        $user = $this->createMock(User::class);
        $user->method('can')->willReturnCallback(fn (string $perm) => $perm === 'menu.siswa');

        $items = [
            [
                'group' => 'Siswa',
                'collapsible' => true,
                'icon' => 'M0 0',
                'pattern' => ['admin.anak.*', 'admin.pendaftaran.*'],
                'sections' => [
                    [
                        'items' => [
                            ['route' => 'admin.anak.index', 'label' => 'Siswa', 'perm' => 'menu.siswa', 'icon' => 'M0 0', 'pattern' => 'admin.anak.*'],
                            ['route' => 'admin.pendaftaran.index', 'label' => 'Daftar', 'perm' => 'menu.pendaftaran', 'icon' => 'M0 0', 'pattern' => 'admin.pendaftaran.*', 'badge' => 3],
                        ],
                    ],
                ],
            ],
        ];

        $built = MenuBuilder::build($items, $user, skipPermFilter: false);

        $this->assertCount(1, $built);
        $this->assertSame(1, $built[0]['count']);
        $this->assertFalse($built[0]['has_badge_warning']);
    }

    public function test_count_includes_nested_subgroup_links(): void
    {
        $user = $this->createMock(User::class);

        $items = [
            [
                'group' => 'Parent',
                'collapsible' => true,
                'icon' => 'M0 0',
                'pattern' => 'admin.*',
                'sections' => [
                    [
                        'items' => [
                            [
                                'group' => 'Child',
                                'collapsible' => true,
                                'icon' => 'M0 0',
                                'pattern' => 'admin.child.*',
                                'sections' => [
                                    [
                                        'items' => [
                                            ['route' => 'admin.child.a', 'label' => 'A', 'icon' => 'M0 0', 'pattern' => 'admin.child.*'],
                                            ['route' => 'admin.child.b', 'label' => 'B', 'icon' => 'M0 0', 'pattern' => 'admin.child.*'],
                                        ],
                                    ],
                                ],
                            ],
                            ['route' => 'admin.top', 'label' => 'Top', 'icon' => 'M0 0', 'pattern' => 'admin.top.*'],
                        ],
                    ],
                ],
            ],
        ];

        $built = MenuBuilder::build($items, $user, skipPermFilter: true);

        $this->assertSame(3, $built[0]['count']);
    }

    public function test_has_badge_warning_when_visible_child_has_badge(): void
    {
        $user = $this->createMock(User::class);

        $items = [
            [
                'group' => 'Siswa',
                'collapsible' => true,
                'icon' => 'M0 0',
                'pattern' => 'admin.*',
                'sections' => [
                    [
                        'items' => [
                            ['route' => 'admin.pendaftaran.index', 'label' => 'Daftar', 'icon' => 'M0 0', 'pattern' => 'admin.pendaftaran.*', 'badge' => 2],
                        ],
                    ],
                ],
            ],
        ];

        $built = MenuBuilder::build($items, $user, skipPermFilter: true);

        $this->assertTrue($built[0]['has_badge_warning']);
        $this->assertSame(1, $built[0]['count']);
    }

    public function test_role_gate_hides_item_when_user_lacks_role(): void
    {
        $user = $this->createMock(User::class);
        $user->method('hasAnyRole')->willReturn(false);

        $items = [
            [
                'group' => 'Kelas saya',
                'items' => [
                    ['route' => 'adminkelas.anak.index', 'label' => 'Siswa Kelasku', 'roles' => ['Wali Kelas'], 'icon' => 'M0 0', 'pattern' => 'adminkelas.anak.*'],
                    ['route' => 'admin.anak.index', 'label' => 'Siswa', 'perm' => 'menu.siswa', 'icon' => 'M0 0', 'pattern' => 'admin.anak.*'],
                ],
            ],
        ];

        $built = MenuBuilder::build($items, $user, skipPermFilter: true);

        $this->assertCount(1, $built[0]['items']);
        $this->assertSame('admin.anak.index', $built[0]['items'][0]['route']);
    }
}
