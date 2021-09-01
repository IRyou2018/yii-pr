<?php
/**
 *
 * // dome  
 * // 下载的文件名
 * $filename = 'test';
 * // title行
 * $titles = ['title1'];
 * SimpleExcel::echoBegin($filename, $titles);
 * $data = [
 *     ['1a'], // 第一行
 *     ['2a', '2b'], // 第二行
 * ];
 * SimpleExcel::echoRows($data);
 * // 追加行
 * SimpleExcel::echoRows($data);
 * // 输出
 * SimpleExcel::echoFinish();
 *
 * 注意不要下载数据量太大的，会非常慢，而且excel有行数限制
 */
class SimpleExcel
{
    /**
     * 设置请求头
     * @param string $filename [description]
     */
    private static function setHeaders($filename = 'data')
    {
        // 检验header头是否已经发送
        if (headers_sent($file, $line)) {
            echo 'Header already sent @ ' . $file . ':' . $line;
            return;
        }
        //header('Cache-Control: no-cache;must-revalidate'); //fix ie download bug
        header('Pragma: no-cache, no-store');
        header("Expires: Wed, 26 Feb 1997 08:21:57 GMT");
        header('Content-type: application/force-download;charset=utf-8');
        header("Content-Disposition: attachment; filename=\"" . $filename . '"');
    }
    /**
     *
     * @param  [type] $filename [description]
     * @param  array  $titles   [description]
     * @return [type]           [description]
     */
    public static function echoBegin($filename = '', $titles = [])
    {
        if (empty($filename)) {
            $filename = date("Y-m-d");
        }
        self::setHeaders($filename . '.xls');
        echo '
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <style>
                td{vnd.ms-excel.numberformat:@}
            </style>
        </head>';
        echo '<table width="100%" border="1">';
        echo '<tr><th filter=all>' . implode('</th><th filter=all>', $titles) . "</th></tr>\r\n";
        flush();
    }
    public static function echoRows($rows)
    {
        foreach ($rows as $row) {
            echo '<tr><td>' . implode('</td><td>', $row) . "</td></tr>\r\n";
        }
        flush();
    }
    public static function echoFinish()
    {
        echo '</table>';
        flush();
    }
}