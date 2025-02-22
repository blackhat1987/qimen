<?php
require 'qimen.php';

use com\nlf\calendar\Solar;

    // 获取表单提交的数据或设置默认值
    $selectedDate = $_POST['selected_date'] ?? (new DateTime())->format('Y-m-d H:i'); // 用户选择的公历时间

    $solar = Solar::fromDate(new DateTime($selectedDate));
    $lunar = $solar->getLunar();
    
    // 设置时间相关变量
    $solarDate = $solar->toYmd() . ' ' . $solar->getHour() . ':' . $solar->getMinute();
    $lunarDate = $lunar->toString();
    
    // 创建实例
    $qimen = new QiMen($lunar);
    
    // 获取节气信息
    $jieqi = $lunar->getJieQi();
    if (empty($jieqi)) {
        $prevJieQi = $lunar->getPrevJieQi();
        $nextJieQi = $lunar->getNextJieQi();
        $jieqi = "{$prevJieQi->getName()}（{$prevJieQi->getSolar()->toYmd()}） ~  {$nextJieQi->getName()}（{$nextJieQi->getSolar()->toYmd()}）";
    }

    // 获取干支
    $ganzhi = $lunar->getYearInGanZhi() . ' ' . 
              $lunar->getMonthInGanZhi() . ' ' . 
              $lunar->getDayInGanZhi() . ' ' . 
              $lunar->getTimeInGanZhi() . ' '; 
    
    // 获取旬空
    $nianKong = $lunar->getYearXunKong();
    $yueKong = $lunar->getMonthXunKong();
    $riKong = $lunar->getDayXunKong();
    $shiKong = $lunar->getTimeXunKong();
    
    // 获取局数信息
    $yuan = $qimen->calcYuan();
    $yinyang= $qimen->calcYinYang();
    $ju = $qimen->calcJu();

    // 获取符头
    $fu = $lunar->getDayInGanZhi();
    $arr = [];
    for ($i = 0; $i < mb_strlen($fu); $i++) {
        $arr[] = mb_substr($fu, $i, 1);
    }
    $fuTou = $qimen->calcFuTou($arr); // 将字符串转换回数组形式

    // 获取时辰干支旬首
    $xunShou = $qimen->getShixun(); // 获取旬首

   // 获取值符星和位置
    list($zhiFu, $zhiFuIndex) = $qimen->getZhiFu();
    list($zhiShi, $zhiShiIndex) = $qimen->getZhiShi();

    // 处理表单提交
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 获取当前日期和时间
        $currentDateTime = new DateTime($selectedDate);

        // 根据按钮的不同值调整日期和时间
        if (isset($_POST['prev_day'])) {
            $currentDateTime->modify('-1 day');
        } elseif (isset($_POST['next_day'])) {
            $currentDateTime->modify('+1 day');
        } elseif (isset($_POST['prev_hour'])) {
            $currentDateTime->modify('-1 hour');
        } elseif (isset($_POST['next_hour'])) {
            $currentDateTime->modify('+1 hour');
        } elseif (isset($_POST['submit'])) {
            // 当前时间按钮，设置为当前时间
            $currentDateTime = new DateTime(); // 获取当前时间
        }

        // 更新选定的日期
        $selectedDate = $currentDateTime->format('Y-m-d H:i');

        // 重新计算 Solar 和 Lunar 对象
        $solar = Solar::fromDate(new DateTime($selectedDate));
        $lunar = $solar->getLunar();

        // 更新日期相关变量
        $solarDate = $solar->toYmd() . ' ' . $solar->getHour() . ':' . $solar->getMinute();
        $lunarDate = $lunar->toString();
    }

    // 提取年、月、日和时
    $currentYear = (new DateTime($selectedDate))->format('Y');
    $currentMonth = (new DateTime($selectedDate))->format('n');
    $currentDay = (new DateTime($selectedDate))->format('j');
    $currentHour = (new DateTime($selectedDate))->format('G');

    // 输出HTML部分
    echo '<!DOCTYPE html>';
    echo '<html>';
    echo '<head>';
    echo '<title>盘</title>';
    echo '</head>';
    echo '<body>';
    echo '<div class="container">';
    echo '<h2 style="text-align: center; margin-bottom: 20px;">盘</h2>';
    echo '<form method="POST" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" class="datetime-form">';
        echo '<input type="hidden" name="selected_date" value="' . htmlspecialchars($selectedDate) . '">';
        echo '<div class="datetime-row">';
            echo '<div class="form-group">';
                echo '<label for="year">年份:</label>';
                echo '<select id="year" name="year">';
                for ($i = 1900; $i <= 2100; $i++) {
                    echo "<option value='{$i}'" . ($i == $currentYear ? ' selected' : '') . ">{$i}</option>";
                }
                echo '</select>';
            echo '</div>';

            echo '<div class="form-group">';
                echo '<label for="month">月份:</label>';
                echo '<select id="month" name="month">';
                for ($i = 1; $i <= 12; $i++) {
                    echo "<option value='{$i}'" . ($i == $currentMonth ? ' selected' : '') . ">{$i}</option>";
                }
                echo '</select>';
            echo '</div>';

            echo '<div class="form-group">';
                echo '<label for="day">日期:</label>';
                echo '<select id="day" name="day">';
                for ($i = 1; $i <= 31; $i++) {
                    echo "<option value='{$i}'" . ($i == $currentDay ? ' selected' : '') . ">{$i}</option>";
                }
                echo '</select>';
            echo '</div>';

            echo '<div class="form-group">';
                echo '<label for="hour">时辰:</label>';
                echo '<select id="hour" name="hour">';
                $timeNames = [
                    '23,0' => '子时(23:00-01:00)',
                    '1,2' => '丑时(01:00-03:00)',
                    '3,4' => '寅时(03:00-05:00)',
                    '5,6' => '卯时(05:00-07:00)',
                    '7,8' => '辰时(07:00-09:00)',
                    '9,10' => '巳时(09:00-11:00)',
                    '11,12' => '午时(11:00-13:00)',
                    '13,14' => '未时(13:00-15:00)',
                    '15,16' => '申时(15:00-17:00)',
                    '17,18' => '酉时(17:00-19:00)',
                    '19,20' => '戌时(19:00-21:00)',
                    '21,22' => '亥时(21:00-23:00)'
                ];
                foreach ($timeNames as $value => $label) {
                    $hours = explode(',', $value);
                    $selected = in_array($currentHour, $hours) ? ' selected' : '';
                    echo "<option value='{$value}'{$selected}>{$label}</option>";
                }
                echo '</select>';
            echo '</div>';
        echo '</div>';

        echo '<div class="button-row">';
            echo '<button type="submit" name="prev_day" value="1" class="btn btn-secondary">上一日</button>';
            echo '<button type="submit" name="next_day" value="1" class="btn btn-secondary">下一日</button>';
            echo '<button type="submit" name="prev_hour" value="1" class="btn btn-secondary">上一时</button>';
            echo '<button type="submit" name="next_hour" value="1" class="btn btn-secondary">下一时</button>';
            echo '<button type="submit" name="submit" value="1" class="btn btn-primary">当前时间</button>';
        echo '</div>';
    echo '</form>';

    // 信息显示部分使用新的样式
    echo '<div class="info-group">';
    echo "<span>公历时间：{$solarDate}</span>";
    echo "<span>农历时间：{$lunarDate}</span>";
    echo '</div>';

    echo '<div class="info-group">';
    echo "<span>节气：{$jieqi}</span>";
    echo '</div>';

    echo '<div class="info-group">';
    echo "<span>干支：{$ganzhi}</span>";
    echo '</div>';

    echo '<div class="info-group">';
    echo "<span>旬空：{$nianKong} {$yueKong} {$riKong} {$shiKong}</span>";
    echo '</div>';

    // 旬首
    echo '<div class="info-group">';
    echo "<span>当日符头：{$fuTou}&nbsp;&nbsp;&nbsp;&nbsp;时辰旬首：{$xunShou}</span>";
    echo '</div>';

    // 局数
    echo '<div class="info-group">';
    echo "<span>局数：{$yuan} {$yinyang} {$ju}局</span>";
    echo '</div>';
    // 值符
    echo '<div class="info-group">';
    echo "<span>值符：天{$zhiFu}&nbsp;&nbsp;&nbsp;&nbsp;值使：{$zhiShi}门</span>";
    echo '</div>';
   
    
    $diPan = $qimen->paiDiPan($yinyang, $ju);
    $jiuXing = $qimen->paiJiuXing($zhiFu);
    $baShen = $qimen->paiBashen($zhiFu);
    $tianPan = $qimen->paiTianPan($zhiFu);
    $baMen = $qimen->paiBamen($zhiShi);
   
    echo '<table style="border-collapse: collapse; width: 100%;">';
    echo '<tr>';
        echo '<td style="border: 1px solid #000; width: 100px; height: 150px; padding: 15px;">';
            echo '<div style="display: flex; justify-content: space-between; height: 100%;">';
            echo '<div style="text-align: left; padding: 5px;">';
            echo ((isset($baShen[4]) ? $baShen[4] : '') . '<br>' . (isset($jiuXing[4]) ? $jiuXing[4] : '')) . '<br>' . (isset($baMen[4]) ? $baMen[4] : '');
            echo '</div>';
            echo '<div style="text-align: right; padding: 5px;">';
            echo ((isset($tianPan[4]) ? $tianPan[4] : '') . '<br>' . (isset($diPan[4]) ? $diPan[4] : ''));
            echo '</div>';
            echo '</div>';
        echo '</td>';
        echo '<td style="border: 1px solid #000; width: 100px; height: 100px; padding: 15px;">';
            echo '<div style="display: flex; justify-content: space-between; height: 100%;">';
            echo '<div style="text-align: left; padding: 5px;">';
            echo ((isset($baShen[9]) ? $baShen[9] : '') . '<br>' . (isset($jiuXing[9]) ? $jiuXing[9] : '')) . '<br>' . (isset($baMen[9]) ? $baMen[9] : '');
            echo '</div>';
            echo '<div style="text-align: right; padding: 5px;">';
            echo ((isset($tianPan[9]) ? $tianPan[9] : '') . '<br>' . (isset($diPan[9]) ? $diPan[9] : ''));
            echo '</div>';
            echo '</div>';
        echo '</td>';
        echo '<td style="border: 1px solid #000; width: 100px; height: 100px; padding: 15px;">';
            echo '<div style="display: flex; justify-content: space-between; height: 100%;">';
            echo '<div style="text-align: left; padding: 5px;">';
            echo ((isset($baShen[2]) ? $baShen[2] : '') . '<br>' . (isset($jiuXing[2]) ? $jiuXing[2] : '')) . '<br>' . (isset($baMen[2]) ? $baMen[2] : '');
            echo '</div>';
            echo '<div style="text-align: right; padding: 5px;">';
            echo ((isset($tianPan[2]) ? $tianPan[2] : '') . '<br>' . (isset($diPan[2]) ? $diPan[2] : ''));
            echo '</div>';
            echo '</div>';
        echo '</td>';
    echo '</tr>';
    echo '<tr>';
        echo '<td style="border: 1px solid #000; width: 100px; height: 100px; padding: 15px;">';
            echo '<div style="display: flex; justify-content: space-between; height: 100%;">';
            echo '<div style="text-align: left; padding: 5px;">';
            echo ((isset($baShen[3]) ? $baShen[3] : '') . '<br>' . (isset($jiuXing[3]) ? $jiuXing[3] : '')) . '<br>' . (isset($baMen[3]) ? $baMen[3] : '');
            echo '</div>';
            echo '<div style="text-align: right; padding: 5px;">';
            echo ((isset($tianPan[3]) ? $tianPan[3] : '') . '<br>' . (isset($diPan[3]) ? $diPan[3] : ''));
            echo '</div>';
            echo '</div>';
        echo '</td>';
        echo '<td style="border: 1px solid #000; width: 100px; height: 100px; padding: 15px;">';
            echo '<div style="display: flex; justify-content: space-between; height: 100%;">';
            echo '<div style="text-align: left; padding: 5px;">';
            echo ((isset($baShen[5]) ? $baShen[5] : '') . '<br>' . (isset($jiuXing[5]) ? $jiuXing[5] : '')) . '<br>' . (isset($baMen[5]) ? $baMen[5] : '');
            echo '</div>';
            echo '<div style="text-align: right; padding: 5px;">';
            echo ((isset($tianPan[5]) ? $tianPan[5] : '') . '<br>' . (isset($diPan[5]) ? $diPan[5] : ''));
            echo '</div>';
            echo '</div>';
        echo '</td>';
        echo '<td style="border: 1px solid #000; width: 100px; height: 100px; padding: 15px;">';
            echo '<div style="display: flex; justify-content: space-between; height: 100%;">';
            echo '<div style="text-align: left; padding: 5px;">';
            echo ((isset($baShen[7]) ? $baShen[7] : '') . '<br>' . (isset($jiuXing[7]) ? $jiuXing[7] : '')) . '<br>' . (isset($baMen[7]) ? $baMen[7] : '');
            echo '</div>';
            echo '<div style="text-align: right; padding: 5px;">';
            echo ((isset($tianPan[7]) ? $tianPan[7] : '') . '<br>' . (isset($diPan[7]) ? $diPan[7] : ''));
            echo '</div>';
            echo '</div>';
        echo '</td>';
    echo '</tr>';
    echo '<tr>';
        echo '<td style="border: 1px solid #000; width: 100px; height: 100px; padding: 15px;">';
            echo '<div style="display: flex; justify-content: space-between; height: 100%;">';
            echo '<div style="text-align: left; padding: 5px;">';
            echo ((isset($baShen[8]) ? $baShen[8] : '') . '<br>' . (isset($jiuXing[8]) ? $jiuXing[8] : '')) . '<br>' . (isset($baMen[8]) ? $baMen[8] : '');
            echo '</div>';
            echo '<div style="text-align: right; padding: 5px;">';
            echo ((isset($tianPan[8]) ? $tianPan[8] : '') . '<br>' . (isset($diPan[8]) ? $diPan[8] : ''));
            echo '</div>';
            echo '</div>';
        echo '</td>';
        echo '<td style="border: 1px solid #000; width: 100px; height: 100px; padding: 15px;">';
            echo '<div style="display: flex; justify-content: space-between; height: 100%;">';
            echo '<div style="text-align: left; padding: 5px;">';
            echo ((isset($baShen[1]) ? $baShen[1] : '') . '<br>' . (isset($jiuXing[1]) ? $jiuXing[1] : '')) . '<br>' . (isset($baMen[1]) ? $baMen[1] : '');
            echo '</div>';
            echo '<div style="text-align: right; padding: 5px;">';
            echo ((isset($tianPan[1]) ? $tianPan[1] : '') . '<br>' . (isset($diPan[1]) ? $diPan[1] : ''));
            echo '</div>';
            echo '</div>';
        echo '</td>';
        echo '<td style="border: 1px solid #000; width: 100px; height: 100px; padding: 15px;">';
            echo '<div style="display: flex; justify-content: space-between; height: 100%;">';
            echo '<div style="text-align: left; padding: 5px;">';
            echo ((isset($baShen[6]) ? $baShen[6] : '') . '<br>' . (isset($jiuXing[6]) ? $jiuXing[6] : '')) . '<br>' . (isset($baMen[6]) ? $baMen[6] : '');
            echo '</div>';
            echo '<div style="text-align: right; padding: 5px;">';
            echo ((isset($tianPan[6]) ? $tianPan[6] : '') . '<br>' . (isset($diPan[6]) ? $diPan[6] : ''));
            echo '</div>';
            echo '</div>';
        echo '</td>';
    echo '</tr>';
    echo '</table>';

    echo '</div>';
    echo '</body>';
    echo '</html>';

?>

<link href="css.css" rel="stylesheet" type="text/css" />