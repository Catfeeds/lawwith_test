<?php
namespace Admin\Model;
use PHPExcel;

class ExcelModel extends Model {


//导入excel内容转换成数组 
public function import($filePath){
  $PHPExcel = new PHPExcel(); 

  /**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/ 
  $PHPReader = new PHPExcel_Reader_Excel2007(); 
	if(!$PHPReader->canRead($filePath)){ 
	  $PHPReader = new PHPExcel_Reader_Excel5(); 
	  if(!$PHPReader->canRead($filePath)){ 
		echo 'no Excel'; 
		return; 
	  } 
	} 
  
  $PHPExcel = $PHPReader->load($filePath); 
  $currentSheet = $PHPExcel->getSheet(0);  //读取excel文件中的第一个工作表
  $allColumn = $currentSheet->getHighestColumn(); //取得最大的列号
  $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
  $erp_orders_id = array();  //声明数组
  
  /**从第二行开始输出，因为excel表中第一行为列名*/ 
  for($currentRow = 1;$currentRow <= $allRow;$currentRow++){ 
  
  	/**从第A列开始输出*/ 
	for($currentColumn= 'A';$currentColumn<= $allColumn; $currentColumn++){ 
	
	  $val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65,$currentRow)->getValue();/**ord()将字符转为十进制数*/
	  if($val!=''){
	  $erp_orders_id[] = $val; 
	  }
	  /**如果输出汉字有乱码，则需将输出内容用iconv函数进行编码转换，如下将gb2312编码转为utf-8编码输出*/ 
	  //echo iconv('utf-8','gb2312', $val)."\t"; 
	  
	} 
  } 
  return $erp_orders_id;
}

//导出Excel表格
public function export($data,$excelFileName,$sheetTitle){

    $this->__construct();
    /* 实例化类 */
    $objPHPExcel = new PHPExcel(); 
    
    /* 设置输出的excel文件为2007兼容格式 */
    //$objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);//非2007格式
    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    
    /* 设置当前的sheet */
    $objPHPExcel->setActiveSheetIndex(0);
	
    $objActSheet = $objPHPExcel->getActiveSheet();
    
	/*设置宽度*/
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(60);
	
	
		
    /* sheet标题 */
    $objActSheet->setTitle($sheetTitle);
    
    $i = 2;
    foreach($data as $value)
    {
        /* excel文件内容 */
        $j = 'A';
        foreach($value as $value2)
        { 
//            $value2=iconv("gbk","utf-8",$value2);
            $objActSheet->setCellValue($j.$i,$value2);
            $j++;
        }
        $i++;
    }
    
	
    /* 生成到浏览器，提供下载 */ 
	ob_end_clean();  //清空缓存			 
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
    header("Content-Type:application/force-download");
    header("Content-Type:application/vnd.ms-execl");
    header("Content-Type:application/octet-stream");
    header("Content-Type:application/download");
    header('Content-Disposition:attachment;filename="'.$excelFileName.'.xlsx"');
    header("Content-Transfer-Encoding:binary"); 
    $objWriter->save('php://output');
}


}
?> 