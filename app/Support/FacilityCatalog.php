<?php

namespace App\Support;

class FacilityCatalog
{
    /**
     * Master preset list of facilities admins can pick from.
     * Each entry: key => ['label' => ..., 'icon' => inline SVG path markup].
     */
    public static function options(): array
    {
        return [
            'fotografer' => ['label' => 'Fotografer', 'icon' => self::ICON_CAMERA],
            'wasit' => ['label' => 'Wasit', 'icon' => self::ICON_FLAG],
            'jersey' => ['label' => 'Jersey Inventaris', 'icon' => self::ICON_SHIRT],
            'minum' => ['label' => 'Air Minum', 'icon' => self::ICON_WATER],
            'p3k' => ['label' => 'P3K', 'icon' => self::ICON_MEDKIT],
            'durasi_main' => ['label' => 'Durasi Main', 'icon' => self::ICON_CLOCK],
            'konsumsi' => ['label' => 'Konsumsi', 'icon' => self::ICON_BAG],
            'dokumentasi_video' => ['label' => 'Dokumentasi Video', 'icon' => self::ICON_VIDEO],
        ];
    }

    public static function label(string $key): string
    {
        return self::options()[$key]['label'] ?? $key;
    }

    public static function icon(string $key): string
    {
        return self::options()[$key]['icon'] ?? self::ICON_DEFAULT;
    }

    private const ICON_CAMERA = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22a2 2 0 001.664.89H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>';

    private const ICON_FLAG = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18M3 4h13l-1.5 4L16 12H3"/>';

    private const ICON_SHIRT = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 4L4 8l3 3v9h10v-9l3-3-5-4-2 2h-2L9 4z"/>';

    private const ICON_WATER = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3s6 6.5 6 10.5a6 6 0 11-12 0C6 9.5 12 3 12 3z"/>';

    private const ICON_MEDKIT = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8a2 2 0 012-2h12a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2V8z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 6V5a3 3 0 013-3v0a3 3 0 013 3v1M12 11v4m-2-2h4"/>';

    private const ICON_CLOCK = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>';

    private const ICON_BAG = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 11H4L5 9z"/>';

    private const ICON_VIDEO = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>';

    private const ICON_DEFAULT = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>';
}
