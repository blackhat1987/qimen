<?php

require 'Lunar.php';

class QiMen
{
	// 静态属性定义
	public static $SQLY_YANG = ['戊', '己', '庚', '辛', '壬', '癸', '乙', '丙', '丁'];
	public static $SQLY_YIN = ['戊', '己', '庚', '辛', '壬', '癸', '丁', '丙', '乙'];
	public static $STARS = ['蓬', '任', '冲', '辅', '英', '芮', '柱', '心', '禽'];
	public static $DOORS = ['休', '生', '伤', '杜', '景', '死', '惊', '开'];
	public static $SPIRITS = ['符', '蛇', '阴', '合', '虎', '武', '地', '天'];
	
	// 节气对应的局数定义
	public static $JIE_QI_JU = [
		'yang' => [
			'冬至' => [1, 7, 4], '惊蛰' => [1, 7, 4], '小寒' => [2, 8, 5], '大寒' => [3, 9, 6],
			'春分' => [3, 9, 6], '芒种' => [6, 3, 9], '谷雨' => [5, 2, 8], '小满' => [5, 2, 8],
			'立春' => [8, 5, 2], '清明' => [4, 1, 7], '立夏' => [4, 1, 7], '雨水' => [9, 6, 3]
		],
		'yin' => [
			'夏至' => [9, 3, 6], '白露' => [9, 3, 6], '立冬' => [6, 9, 3], '寒露' => [6, 9, 3],
			'大暑' => [7, 1, 4], '秋分' => [7, 1, 4], '小暑' => [8, 2, 5], '立秋' => [2, 5, 8],
			'处暑' => [1, 4, 7], '霜降' => [5, 8, 2], '小雪' => [5, 8, 2], '大雪' => [4, 7, 1]
		]
	];

	//public static $surroundingPositions = [1, 8, 3, 4, 9, 2, 7, 6]; // 坎艮震巽离坤兑乾

	public static $LIU_JIA_ZI_XUN_SHOU = [
		'甲子' => ['甲子戊'],
		'甲戌' => ['甲戌己'],
		'甲申' => ['甲申庚'],
		'甲午' => ['甲午辛'],
		'甲辰' => ['甲辰壬'],
		'甲寅' => ['甲寅癸']
	];

	public static $palaces = [
		// 宫位的相关信息数组，包含门、星、数字代号的对应值
		"巽" => ["门" => "杜", "星" => "辅", "no" => 4],
		"离" => ["门" => "景", "星" => "英", "no" => 9],
		"坤" => ["门" => "死", "星" => "芮", "no" => 2],
		"震" => ["门" => "伤", "星" => "冲", "no" => 3],
		"兑" => ["门" => "惊", "星" => "柱", "no" => 7],
		"乾" => ["门" => "开", "星" => "心", "no" => 6],
		"艮" => ["门" => "生", "星" => "任", "no" => 8],
		"坎" => ["门" => "休", "星" => "蓬", "no" => 1],
		"中" => ["门" => "  ", "星" => "禽", "no" => 5]
	];
	
	// 实例属性定义
	private $lunar;
	private $yinYang;
	private $yuan; 
 	private $tianPan = [];
	private $stars = [];
	private $doors = [];
	private $spirits = [];
	private $zhiFu;
	private $zhiShi; 
	
	private $ju;
	private $diPan = [];
	
	private $zhiFuPos;
	private $zhiShiPos;
	
	// 从静态属性复制的实例属性
	private $sqly = [];
	private $starsArray = [];
	private $doorsArray = [];
	private $spiritsArray = [];

	/**
	 * 构造函数
	 * @param Lunar $lunar 农历日期对象
	 */
	public function __construct($lunar)
	{
		$this->lunar = $lunar;
		
		// 初始化所有数组属性
		$this->sqly = self::$SQLY_YANG;
		$this->sqly = self::$SQLY_YIN;
		$this->starsArray = self::$STARS;
		$this->doorsArray = self::$DOORS;
		$this->spiritsArray = self::$SPIRITS;
		
		// 初始化空数组
		$this->diPan = array_fill(1, 9, '');
		$this->tianPan = array_fill(1, 9, '');
		$this->stars = array_fill(1, 9, '');
		$this->doors = array_fill(1, 9, '');
		$this->spirits = array_fill(1, 9, '');
		 
		// 开始初始化
		$this->init();
	}

	/**
	 * 初始化奇门遁甲盘
	 * 按照传统顺序计算和布置各项要素
	 */
	private function init()
	{
		// 1. 确定阴阳遁
		$this->calcYinYang();
		
		// 2. 确定三元
		$this->yuan = $this->calcYuan();
		
		// 3. 确定局数
		$this->ju = $this->calcJu();
		
		// 4. 排布地盘天干
		$this->diPan = $this->paiDiPan($this->yinYang, $this->ju);	
		
		// 5. 确定值符
		list($this->zhiFu, $this->zhiFuPos) = $this->getZhiFu();
		
		// 6. 确定值使
		list($this->zhiShi, $this->zhiShiPos) = $this->getZhiShi();
		
		// 7. 布局九星
		$this->stars = $this->paiJiuXing($this->zhiFuPos);
		
		// 8. 布局八门
		$this->doors = $this->paiBamen($this->zhiShiPos);
		
		// 9. 布局八神
		$this->spirits = $this->paiBashen($this->zhiFuPos);

		// 10. 排布天盘天干
		$this->tianPan = $this->paiTianPan($this->zhiFuPos);
	}

	// 函数calculateFuTou用于计算干支符头
	// 参数$currentGanZhi是一个包含当前干支信息的数组，格式为 ["天干地支"]
	function calcFuTou($currentGanZhi) 
	{
		$tiangan = ["甲", "乙", "丙", "丁", "戊", "己", "庚", "辛", "壬", "癸"];
		$dizhi = ["子", "丑", "寅", "卯", "辰", "巳", "午", "未", "申", "酉", "戌", "亥"];
		$gan = $currentGanZhi[0];
		$zhi = $currentGanZhi[1];

		// 检查当前干支是否为符头
		if ($gan == "甲" || $gan == "己") {
			return $gan . $zhi; // 直接返回当前干支
		}

		$ganIndex = array_search($gan, $tiangan);
		$zhiIndex = array_search($zhi, $dizhi);
		do {
			$ganIndex = ($ganIndex - 1 + 10) % 10;
			$gan = $tiangan[$ganIndex];
			$zhiIndex = ($zhiIndex - 1 + 12) % 12;
			$zhi = $dizhi[$zhiIndex];
		} while ($gan != "甲" && $gan != "己");
		return $gan . $zhi;
	} 

	/**
	 * 计算阴阳遁
	 * 根据节气判断阴阳遁，不依赖具体时刻
	 */
	function calcYinYang()
	{
		// 获取当前节气
		$term = $this->lunar->getPrevJieQi();
		$prevTermName = $term->getName();
		//echo "当前节气: {$prevTermName}";

		// 判断阴阳遁
		if ($term === '冬至' || $term === '夏至') {
			// 冬至前是阴遁，冬至后是阳遁
			$this->yinYang = ($term === '冬至') ? '阴遁' : '阳遁';
		} else {
			// 根据节气表判断
			if (isset(self::$JIE_QI_JU['yang'][$prevTermName])) {
				$this->yinYang = '阳遁';
			} elseif (isset(self::$JIE_QI_JU['yin'][$prevTermName])) {
				$this->yinYang = '阴遁';
			} else {
				// 默认值或错误处理
				$this->yinYang = '未知';
			}
		}
		return $this->yinYang;
	}

	/**
	 * 计算三元
	 * 优化后的三元判断逻辑，符合传统规则
	 */
	public function calcYuan()
	{
		// 获取日柱天干和地支
		$dayGan = $this->lunar->getDayGan();
		$dayZhi = $this->lunar->getDayZhi();
		
		// 非符头日的干支组合判断
		$dayGanZhi = $dayGan . $dayZhi;
		//echo "</br>";
		//echo "日干: {$dayGanZhi}";
		
		// 上元干支组合
		$shangYuan = [
			'甲子', '乙丑', '丙寅', '丁卯', '戊辰',
			'己卯', '庚辰', '辛巳', '壬午', '癸未',
			'甲午', '乙未', '丙申', '丁酉', '戊戌',
			'己酉', '庚戌', '辛亥', '壬子', '癸丑'
		];
		
		// 中元干支组合
		$zhongYuan = [
			'己巳', '庚午', '辛未', '壬申', '癸酉',
			'甲申', '乙酉', '丙戌', '丁亥', '戊子',
			'己亥', '庚子', '辛丑', '壬寅', '癸卯',
			'甲寅', '乙卯', '丙辰', '丁巳', '戊午'
		];
		
		// 下元干支组合
		$xiaYuan = [
			'甲戌', '乙亥', '丙子', '丁丑', '戊寅',
			'己丑', '庚寅', '辛卯', '壬辰', '癸巳',
			'甲辰', '乙巳', '丙午', '丁未', '戊申',
			'己未', '庚申', '辛酉', '壬戌', '癸亥'
		];
		
		if (in_array($dayGanZhi, $shangYuan)) {
			$this->yuan = '上元'; // 确保设置了 $this->yuan
			return '上元';
		}
		if (in_array($dayGanZhi, $zhongYuan)) {
			$this->yuan = '中元'; // 确保设置了 $this->yuan
			return '中元';
		}
		if (in_array($dayGanZhi, $xiaYuan)) {
			$this->yuan = '下元'; // 确保设置了 $this->yuan
			return '下元';
		}
		// 设置默认值
		$this->yuan = '未知';
		//echo "获取到的数据: {$this->yuan}";
		return $this->yuan;
	}

	/**
	 * 计算局数
	 * 根据节气和三元确定局数
	 * @return int 返回1-9的局数
	 */
	public function calcJu()
	{
		// 获取当前节气
		$term = $this->lunar->getPrevJieQi();
		$prevTermName = $term->getName();
		// 获取当前三元
		$yuan = $this->calcYuan();
		
		// 根据节气和三元确定局数
		if ($this->yinYang === '阳遁') {
			$juArray = self::$JIE_QI_JU['yang'][$prevTermName] ?? null;
		} else {
			$juArray = self::$JIE_QI_JU['yin'][$prevTermName] ?? null;
		}
		
		if ($juArray) {
			switch ($yuan) {
				case '上元':
					$this->ju = $juArray[0];
					break;
				case '中元':
					$this->ju = $juArray[1];
					break;
				case '下元':
					$this->ju = $juArray[2];
					break;
				default:
					$this->ju = 1; // 默认值或错误处理
					break;
			}
		} else {
			$this->ju = 1; // 默认值或错误处理
		}
		
		return $this->ju;
	}

	/**
     * 获取时辰干支旬首
     * @return string|null 返回对应关系的字符串或者未找到时返回null
     */
    public function getShixun() {
        $xun = $this->lunar->getTimeXun();
        if (isset(self::$LIU_JIA_ZI_XUN_SHOU[$xun])) {
            return implode("", self::$LIU_JIA_ZI_XUN_SHOU[$xun]); // 将数组转换为字符串
        }      
        return null;
    }
	
	/**
	 * 排布地盘天干
	 * @param string $yinYang 阴阳遁类型 ('阴遁' 或 '阳遁')
	 * @param int $ju 局数(1-9)
	 * @return array 返回地盘天干布局
	 */
	public function paiDiPan($yinYang, $ju)
	 {
		 //echo "排地盘干局数: {$ju}</br>";
		 //echo "排地盘干阴阳遁: {$yinYang}</br>";
		 // 初始化地盘天干数组
		 $diPan = array_fill(1, 9, '');
		 
		 // 根据阴阳遁选择对应的天干顺序
		 $ganOrder = ($yinYang === '阳遁') ? self::$SQLY_YANG : self::$SQLY_YIN; // 根据阴阳遁选择天干顺序
		 
		 // 根据局数确定戊的起始位置
		 $startPosition = $ju;
		 
		 // 按阴阳遁方向布局
		 for($i = 0; $i < 9; $i++) {
			 if ($yinYang === '阳遁') {
				 // 阳遁顺排
				 $position = ($startPosition + $i - 1) % 9 + 1;
			 } else {
				 // 阴遁逆排
				 $position = ($startPosition - $i + 8) % 9 + 1;
			 }
			 $diPan[$position] = $ganOrder[$i]; // 使用选择的天干顺序
		 }
		 
		 // 中五宫寄坤二宫的规则
		 if (!empty($diPan[5])) {
			 $diPan[2] .= $diPan[5]; // 将中五宫的天干复制到坤二宫
		 }
		 error_log("地盘干: " . implode(", ", $diPan));
		 return $diPan;
	 }

	public function getZhiFu()
	{
		// 获取旬首
		$xunShou = $this->getShixun();
		// 从旬首字符串中提取最后一个字符
		$character = mb_substr($xunShou, -1, 1, 'UTF-8');

		if ($character !== '') {
			// 在已排布的地盘中查找旬首地支所在的位置
			foreach ($this->diPan as $index => $value) {
				if (strpos($value, $character) !== false) {
					// 使用 $palaces 数组来获取星信息
					foreach (self::$palaces as $palaceName => $palaceInfo) {
						if ($palaceInfo['no'] == $index) {
							$star = $palaceInfo['星'];
							// 确保返回的索引是数字
							return [$star, (int)$index]; // 返回星和宫位
						}
					}
				}
			}
		}
		return [null, null]; // 如果没有找到，返回null
	}
	public function getZhiShi()
	{
		// 获取旬首
		$xunShou = $this->getShixun();
		//echo "旬首: {$xunShou} </br>";
		// 从旬首字符串中提取最后一个字符
		$character = mb_substr($xunShou, -1, 1, 'UTF-8');
		//echo "字符: {$character} </br>";

		if ($character !== '') {
			// 在已排布的地盘中查找旬首地支所在的位置
			foreach ($this->diPan as $index => $value) {
				if (strpos($value, $character) !== false) {
					// 使用 $palaces 数组来获取门信息
					foreach (self::$palaces as $palaceName => $palaceInfo) {
						if ($palaceInfo['no'] == $index) {
							$door = $palaceInfo['门'];
							//echo "door: {$door} </br>";
							//echo "值使: {$door} 位置: {$index}";
							return [$door, $index]; // 返回门和宫位
						}
					}
				}
			}
		}
		return [null, null]; // 如果没有找到，返回null
	}
	/**
	 * 布局九星
	 * @param int $zhiFu 值符位置
	 * @return array 返回九星布局
	 */
	public function paiJiuXing($zhiFuPos)
	{
		$stars = array_fill(1, 9, ''); // 初始化九星数组
		$timeGan = $this->lunar->getTimeGan(); // 获取时辰天干

		// 在地盘中查找时干所在的宫位
		$timeGanPos = null;
		foreach ($this->diPan as $pos => $gan) {
			if (strpos($gan, $timeGan) !== false) {
				$timeGanPos = $pos;
				break;
			}
		}

		// 如果找不到时干位置，返回空数组
		if ($timeGanPos === null) {
			error_log("未找到时干 {$timeGan} 在地盘中的位置");
			return $stars;
		}

		// 获取旬首
		$xunShou = $this->getShixun();
		$xunShouGan = mb_substr($xunShou, -1, 1, 'UTF-8');

		// 如果时干是旬首，使用旬首地支来确定值符星位置
		if ($timeGan === $xunShouGan) {
			foreach ($this->diPan as $pos => $gan) {
				if (strpos($gan, $xunShouGan) !== false) {
					$timeGanPos = $pos;
					break;
				}
			}
		}

		// 获取值符星
		list($this->zhiFu, $this->zhiFuPos) = $this->getZhiFu(); // 使用 getZhiFu 函数获取值符星
		// 将值符星放在时干所在宫位
		$stars[$timeGanPos] = $this->zhiFu; // 值符星
		//echo "时干: {$timeGan} 时干位置: {$timeGanPos} 值符星: {$this->zhiFu} </br>";

		// 找到值符星在九星数组中的索引
		$zhiFuIndex = array_search($this->zhiFu, $this->starsArray);
		if ($zhiFuIndex === false) {
			$zhiFuIndex = 0; // 如果没找到，从第一个位置开始
		}

		// 定义九星排列顺序（围绕中五宫的宫位序列）
		$surroundingPositions = [1, 8, 3, 4, 9, 2, 7, 6]; // 坎艮震巽离坤兑乾

		// 找到时干所在位置在surroundingPositions中的索引
		$startIndex = array_search($timeGanPos, $surroundingPositions);
		if ($startIndex === false) {
			$startIndex = 0; // 如果没找到，从第一个位置开始
		}

		// 按顺时针方向排列其余九星
		for ($i = 1; $i < 8; $i++) {
			// 永远按顺时针方向排列
			$index = ($startIndex + $i) % 8;
			$pos = $surroundingPositions[$index];

			// 计算当前星的索引
			$currentStarIndex = ($zhiFuIndex + $i) % 8;

			// 如果是天芮星，同时放置天禽星
			if ($this->starsArray[$currentStarIndex] === '芮') {
				$stars[$pos] = '芮 禽';
			} else if ($this->starsArray[$currentStarIndex] !== '禽') { // 跳过单独的天禽星
				$stars[$pos] = $this->starsArray[$currentStarIndex];
			}
		}

		// 确保中五宫为空
		$stars[5] = '';

		return $stars;
	}

	/**
	 * 布局八神
	 * @param int $zhiFu 值符位置
	 * @return array 返回八神布局
	 */
	/**
	 * 布局八神
	 * 根据时干在地盘中的位置来排布八神
	 * @return array 返回八神布局
	 */
	public function paiBashen()
	{
		// 初始化八神数组
		$spirits = array_fill(1, 9, '');
		
		// 获取时辰天干
		$timeGan = $this->lunar->getTimeGan();
		
		// 在地盘中查找时干所在的宫位
		$timeGanPos = null;
		foreach ($this->diPan as $pos => $gan) {
			if (strpos($gan, $timeGan) !== false) {
				$timeGanPos = $pos;
				break;
			}
		}
		
		// 如果找不到时干位置，返回空数组
		if ($timeGanPos === null) {
			error_log("未找到时干 {$timeGan} 在地盘中的位置");
			return $spirits;
		}
		
		// 定义标准宫位顺序（不变）
		$positions = [1, 8, 3, 4, 9, 2, 7, 6]; // 坎艮震巽离坤兑乾
		
		// 将值符放在时干所在宫位
		$spirits[$timeGanPos] = $this->spiritsArray[0]; // 值符
		
		// 找到时干所在位置在positions中的索引
		$startIndex = array_search($timeGanPos, $positions);
		if ($startIndex === false) {
			$startIndex = 0;
		}
		
		// 按照阴阳遁方向排列其余八神
		for ($i = 1; $i < 8; $i++) {
			if ($this->yinYang === '阳遁') {
				// 阳遁顺时针
				$index = ($startIndex + $i) % 8;
				$pos = $positions[$index];
			} else {
				// 阴遁逆时针
				$index = ($startIndex - $i + 8) % 8;
				$pos = $positions[$index];
			}
			
			$spirits[$pos] = $this->spiritsArray[$i];
		}
		
		// 确保中五宫为空
		$spirits[5] = '';
		
		return $spirits;
	}
	/**
	 * 布局八门
	 * 根据时辰干支和旬首确定值使门位置，然后顺时针排列其他八门
	 * @return array 返回八门布局
	 */
	public function paiBamen()
	{
		// 初始化八门数组
		$doors = array_fill(1, 9, '');
		
		// 获取时辰天干和地支
		$timeGan = $this->lunar->getTimeGan();
		$timeZhi = $this->lunar->getTimeZhi();
		
		// 获取旬首
		$xunShou = $this->getShixun();
		$xunShouGan = mb_substr($xunShou, -1, 1, 'UTF-8');
		
		// 在地盘中找到旬首天干的位置
		$startPos = null;
		foreach ($this->diPan as $pos => $gan) {
			if (strpos($gan, $xunShouGan) !== false) {
				$startPos = $pos;
				break;
			}
		}
		
		// 定义跳涧顺序
		$tiaoJianOrder = [7, 6, 5, 4, 3, 2, 1, 9, 8]; // 兑-乾-中-巽-震-坤-坎-离-艮
		
		// 从旬首位置开始跳涧查找时辰位置
		$currentPos = $startPos;
		$ganZhiPairs = $this->getGanZhiPairsFromXunShou($xunShou, $timeGan . $timeZhi);
		
		foreach ($ganZhiPairs as $pair) {
			$index = array_search($currentPos, $tiaoJianOrder);
			if ($index === false) {
				$index = 0;
			}
			$currentPos = $tiaoJianOrder[($index + 1) % 9];
		}
		
		// 值使门位置确定后，开始排列八门
		$zhiShiPos = $currentPos;
		
		// 按照顺时针方向排列八门
		$surroundingPositions = [1, 8, 3, 4, 9, 2, 7, 6]; // 坎艮震巽离坤兑乾
		
		// 将值使门放在确定的位置
		$doors[$zhiShiPos] = $this->doorsArray[0]; // 值使门
		
		// 顺时针排列其余七门
		$startIndex = array_search($zhiShiPos, $surroundingPositions);
		if ($startIndex === false) {
			$startIndex = 0;
		}
		
		for ($i = 1; $i < 8; $i++) {
			$index = ($startIndex + $i) % 8;
			$pos = $surroundingPositions[$index];
			if ($pos != 5) { // 跳过中五宫
				$doors[$pos] = $this->doorsArray[$i];
			}
		}
		
		// 确保中五宫为空
		$doors[5] = '';
		
		return $doors;
	} 
	
	/**
	 * 辅助方法：根据旬首获取干支对列表直到目标干支
	 * @param string $xunShou 旬首
	 * @param string $targetGanZhi 目标干支
	 * @return array 干支对列表
	 */
	private function getGanZhiPairsFromXunShou($xunShou, $targetGanZhi)
	{
		$gan = ['甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'];
		$zhi = ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];
		
		$pairs = [];
		$start = array_search(mb_substr($xunShou, 0, 1, 'UTF-8'), $gan);
		$current = $start;
		
		do {
			$currentGanZhi = $gan[$current % 10] . $zhi[($current % 12)];
			$pairs[] = $currentGanZhi;
			$current++;
		} while ($currentGanZhi !== $targetGanZhi);
		
		return $pairs;
	}

	public function paiTianPan()
	{
		$tianPan = array_fill(1, 9, ''); // 初始化天盘数组
		$surroundingPositions = [1, 8, 3, 4, 9, 2, 7, 6]; // 获取九星的排列顺序 坎艮震巽离坤兑乾
	
		// 获取九星的地盘天干映射
		$ganMapping = [];
		foreach ($this->starsArray as $star) {
			foreach (self::$palaces as $palaceName => $palaceInfo) {
				if ($palaceInfo['星'] === $star) {
					$originalPos = $palaceInfo['no'];
					$ganMapping[$star] = $this->diPan[$originalPos];
					break;
				}
			}
		}
	
		// 从值符星开始，按九星顺序排列天盘
		$zhiFuIndex = array_search($this->zhiFu, $this->starsArray);
		for ($i = 0; $i < 8; $i++) {
			$currentStarIndex = ($zhiFuIndex + $i) % 8;
			$currentStar = $this->starsArray[$currentStarIndex];
			$pos = $surroundingPositions[$i];
			$tianPan[$pos] = $ganMapping[$currentStar];
		}
	
		$tianPan[5] = ''; // 确保中五宫为空
		return $tianPan;
	}
}
