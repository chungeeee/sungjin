<?php
namespace App\Chung;

use Log;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class ExcelCustomExport extends DefaultValueBinder implements FromArray,WithHeadings,WithTitle,WithStyles,WithCustomValueBinder
{

    protected $excel_data;
    protected $heading;
    protected $title;
    protected $style;

    public function __construct(array $heading,array $excel_data,$title='sheet1',$style=array(), $textCol=array())
    {
        $this->excel_data   = $excel_data;
        $this->heading      = $heading;
        $this->title        = $title;
        $this->style        = $style;
        $this->textCol      = $textCol;
    }

    public function array(): array
    {
        return $this->excel_data;
    }

    public function headings(): array
    {
        return $this->heading;
    }

    public function title(): String
    {
        return $this->title;
    }

    //style custom
    public function styles(Worksheet $sheet){
        //colspan , rowspan
        if(isset($this->style['merge'])){
            // Log::alert($this->style['merge']);
            foreach($this->style['merge'] as $val){
                $sheet->mergeCells($val);
            }
        }
         //border
        if(isset($this->style['border'])){
            foreach($this->style['border'] as $col => $location){
                $this->style['custom'][$col]['borders']= [ $location =>['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]];
            }
        }
        if( isset($this->style['number']))
        {
            foreach($this->style['number'] as $idx => $val)
            {
                $sheet->getStyle($val)->getNumberFormat()->setFormatCode('0');
            }
        }
        // 수동설정
        if(!isset($this->style['custom'])){
            $this->style['custom'] = [
                1 => [
                    'font' => ['bold'=>true], 
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'FFA6A6A6']],
                    'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
                ]
            ];
        }

        return $this->style['custom'];
    }

    // 설정값 중 텍스트 포맷 지정한 컬럼은 텍스트로 입력
    public function bindValue(Cell $cell, $value)
    {
        if (in_array($cell->getColumn(), $this->textCol))
        {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        else
        {
            return parent::bindValue($cell, $value);
        }
    }

/* 
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],'fill' => ['color'=>'#dbdbdb']],

        ];
    } */

}