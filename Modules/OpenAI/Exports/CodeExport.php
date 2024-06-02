<?php

namespace Modules\OpenAI\Exports;

use Modules\OpenAI\Entities\Code;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithMapping
};

class CodeExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * [Here we need to fetch data from data source]
     * @return [Database Object] [Here we are fetching data from archive table and also user table through Eloquent Relationship]
     */
    public function collection()
    {
        return Code::with(['user:id,name', 'user.metas'])->get();
    }

    /**
     * [Here we are putting Headings of The CSV]
     * @return [array] [Excel Headings]
     */
    public function headings(): array
    {
        return[
            'Name',
            'Code',
            'Creator',
            'Created At'
        ];
    }
    /**
     * [By adding WithMapping you map the data that needs to be added as row. This way you have control over the actual source for each column. In case of using the Eloquent query builder]
     * @param $longArticle $longArticle  [It has archive table info of long article]
     * @return array [comma separated value will be produced]
     */

    public function map($code): array
    {
        return[
            $code->promt,
            $code->code,
            optional($code->user)->name,
            timeZoneFormatDate($code->created_at)
        ];
    }
}
