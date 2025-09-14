<?php

namespace Amicus\FilamentEmployeeManagement\Plugins;

use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Support\Facades\FilamentAsset;
use Tiptap\Core\Extension;
use Tiptap\Nodes\Mention;

class MentionRichContentPlugin implements RichContentPlugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @return array<Extension>
     */
    public function getTipTapPhpExtensions(): array
    {
        return [
            app(Mention::class, [
                'options' => [
                    'HTMLAttributes' => [
                        'class' => 'mention',
                    ],
                ],
            ]),
        ];
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [
            FilamentAsset::getScriptSrc('rich-content-plugins/mention'),
        ];
    }

    /**
     * @return array<\Filament\Forms\Components\RichEditor\RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [];
    }

    /**
     * @return array<\Filament\Actions\Action>
     */
    public function getEditorActions(): array
    {
        return [];
    }
}
