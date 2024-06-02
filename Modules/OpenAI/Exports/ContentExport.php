<?php
namespace Modules\OpenAI\Exports;

use Modules\OpenAI\Entities\Content;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithMapping
};

class ContentExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * [Here we need to fetch data from data source]
     * @return [Database Object] [Here we are fetching data from User table and also role table through Eloquent Relationship]
     */
    public function collection(): collection
    {
        return Content::with(['user:id,name', 'useCase:id,slug,name', 'user.metas'])->whereNull(['parent_id'])->get();
    }

    /**
     * [Here we are putting Headings of The CSV]
     * @return [array] [Excel Headings]
     */
    public function headings(): array
    {
        return[
            'Template',
            'Description',
            'Creator',
            'Model',
            'Language',
            'Created At'
        ];
    }
    /**
     * [By adding WithMapping you map the data that needs to be added as row. This way you have control over the actual source for each column. In case of using the Eloquent query builder]
     * @param [object] $userList [It has users table info and roles table info]
     * @return [array]            [comma separated value will be produced]
     */
    public function map($contentList): array
    {
        return[
            optional($contentList->useCase)->name,
            $contentList->content,
            optional($contentList->user)->name,
            $contentList->model,
            $contentList->language,
            timeZoneFormatDate($contentList->created_at),

        ];
    }
}
