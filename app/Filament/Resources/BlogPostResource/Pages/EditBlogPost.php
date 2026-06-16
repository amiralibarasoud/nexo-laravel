<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()->label('حذف مقاله')];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['published_at']) && $data['status'] === 'published') {
            $data['published_at'] = now();
        }
        return $data;
    }
}
