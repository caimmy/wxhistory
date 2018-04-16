<?php
/**
 * Created by PhpStorm.
 * User: caimm
 * Date: 2018/4/10
 * Time: 22:19
 */

namespace app\utils;


use app\components\simplewidget\PageRender;
use app\models\AppGovLose5yound;
use app\models\AppMgrExtLabel;

class ExportHelper
{
    public static function exportYoungtable($young_list, $title) {
        $CELL_COLUMN_LABEL = ['A', 'B', 'C', 'D', 'E', 'F','G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'];

        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        // 设置表头
        $objectPHPExcel->getActiveSheet()->mergeCells('A1:P1');
        $objectPHPExcel->getActiveSheet()->setCellValue('A1', $title);
        $objectPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(22);
        $objectPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objectPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objectPHPExcel->getActiveSheet()->setCellValue('A2', '序号');
        $objectPHPExcel->getActiveSheet()->setCellValue('B2', '姓名');
        $objectPHPExcel->getActiveSheet()->setCellValue('C2', '性别');
        $objectPHPExcel->getActiveSheet()->setCellValue('D2', '出生年月');
        $objectPHPExcel->getActiveSheet()->setCellValue('E2', '民族');
        $objectPHPExcel->getActiveSheet()->setCellValue('F2', '政治面貌');
        $objectPHPExcel->getActiveSheet()->setCellValue('G2', '健康状况');
        $objectPHPExcel->getActiveSheet()->setCellValue('H2', '兴趣爱好');
        $objectPHPExcel->getActiveSheet()->setCellValue('I2', '户籍');
        $objectPHPExcel->getActiveSheet()->setCellValue('J2', '现居地');
        $objectPHPExcel->getActiveSheet()->setCellValue('K2', '现状（读书或上班）');
        $objectPHPExcel->getActiveSheet()->setCellValue('L2', '常住地或所在单位');
        $objectPHPExcel->getActiveSheet()->setCellValue('M2', '身份证号码');
        $objectPHPExcel->getActiveSheet()->setCellValue('N2', '监护人姓名及联系方式');
        $objectPHPExcel->getActiveSheet()->setCellValue('O2', '个人现状主要表现情况');
        $objectPHPExcel->getActiveSheet()->setCellValue('P2', '形成现状原因');

        foreach ($CELL_COLUMN_LABEL as $col_label) {
            $objectPHPExcel->getActiveSheet()->getStyle($col_label.'2')->getFont()->setBold(true);
        }

        $seq_id = 1;
        $row_id = 3;
        foreach ($young_list as $young_item) {
            if (!empty($young_item)) {
                $objectPHPExcel->getActiveSheet()->setCellValue('A'.$row_id, $seq_id);
                $objectPHPExcel->getActiveSheet()->setCellValue('B'.$row_id, $young_item->name);
                $objectPHPExcel->getActiveSheet()->setCellValue('C'.$row_id, (PageRender::GENDER_FEMALE == $young_item->gender) ? '女' : '男' );
                $objectPHPExcel->getActiveSheet()->setCellValue('D'.$row_id, $young_item->birthday);
                $objectPHPExcel->getActiveSheet()->setCellValue('E'.$row_id, AppMgrExtLabel::getConfigValue(AppMgrExtLabel::EXT_CATALOG_NATIONAL, $young_item->nation));
                $objectPHPExcel->getActiveSheet()->setCellValue('F'.$row_id, AppMgrExtLabel::getConfigValue(AppMgrExtLabel::EXT_CATALOG_POLITICAL, $young_item->political));
                $objectPHPExcel->getActiveSheet()->setCellValue('G'.$row_id, $young_item->healthy);
                $objectPHPExcel->getActiveSheet()->setCellValue('H'.$row_id, $young_item->interest);
                $objectPHPExcel->getActiveSheet()->setCellValue('I'.$row_id, $young_item->housereg);
                $objectPHPExcel->getActiveSheet()->setCellValue('J'.$row_id, $young_item->address);
                $objectPHPExcel->getActiveSheet()->setCellValue('K'.$row_id, AppGovLose5yound::$IN_SCHOOL_STATUS[$young_item->inscholl]);
                $objectPHPExcel->getActiveSheet()->setCellValue('L'.$row_id, $young_item->address);
                $objectPHPExcel->getActiveSheet()->setCellValue('M'.$row_id, $young_item->idcard);
                $objectPHPExcel->getActiveSheet()->setCellValue('N'.$row_id, $young_item->contacter . '/' . $young_item->contact_meth);
                $objectPHPExcel->getActiveSheet()->setCellValue('O'.$row_id, $young_item->descrip);
                $objectPHPExcel->getActiveSheet()->setCellValue('P'.$row_id, $young_item->getReasonList(true));
            }

            $seq_id += 1;
            $row_id += 1;
        }

        ob_end_clean();
        ob_start();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="' . $title . '-' . date('Y年m月d日').'.xls"');
        $objWriter = \PHPExcel_IOFactory::createWriter($objectPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}