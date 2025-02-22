// 填充年份选择框
const yearSelect = document.getElementById('year');
const currentYear = new Date().getFullYear();
for (let i = currentYear - 10; i <= currentYear + 10; i++) {
  const option = document.createElement('option');
  option.value = i;
  option.textContent = i;
  yearSelect.appendChild(option);
}

// 填充月份选择框
const monthSelect = document.getElementById('month');
for (let i = 1; i <= 12; i++) {
  const option = document.createElement('option');
  option.value = i;
  option.textContent = i;
  monthSelect.appendChild(option);
}

// 填充日期选择框
const daySelect = document.getElementById('day');
function updateDaySelect(year, month) {
  daySelect.innerHTML = '';
  const daysInMonth = new Date(year, month, 0).getDate();
  for (let i = 1; i <= daysInMonth; i++) {
    const option = document.createElement('option');
    option.value = i;
    option.textContent = i;
    daySelect.appendChild(option);
  }
}

// 初始化日期选择框
const currentDate = new Date();
const currentMonth = currentDate.getMonth() + 1;
const currentDay = currentDate.getDate();
const currentHourIndex = Math.floor(currentDate.getHours() / 2);
updateDaySelect(currentYear, currentMonth);
yearSelect.value = currentYear;
monthSelect.value = currentMonth;
daySelect.value = currentDay;
const hourSelect = document.getElementById('hour');
hourSelect.selectedIndex = currentHourIndex;

// 前一天按钮点击事件处理函数
function prevDay() {
  const year = parseInt(yearSelect.value);
  const month = parseInt(monthSelect.value);
  let day = parseInt(daySelect.value);
  const date = new Date(year, month - 1, day);
  date.setDate(date.getDate() - 1);
  yearSelect.value = date.getFullYear();
  monthSelect.value = date.getMonth() + 1;
  updateDaySelect(date.getFullYear(), date.getMonth() + 1);
  daySelect.value = date.getDate();
}

// 后一天按钮点击事件处理函数
function nextDay() {
  const year = parseInt(yearSelect.value);
  const month = parseInt(monthSelect.value);
  let day = parseInt(daySelect.value);
  const date = new Date(year, month - 1, day);
  date.setDate(date.getDate() + 1);
  yearSelect.value = date.getFullYear();
  monthSelect.value = date.getMonth() + 1;
  updateDaySelect(date.getFullYear(), date.getMonth() + 1);
  daySelect.value = date.getDate();
}

// 前一个时辰按钮点击事件处理函数
function prevHour() {
  const hourSelect = document.getElementById('hour');
  let currentIndex = hourSelect.selectedIndex;
  if (currentIndex > 0) {
    hourSelect.selectedIndex = currentIndex - 1;
  } else {
    const year = parseInt(yearSelect.value);
    const month = parseInt(monthSelect.value);
    let day = parseInt(daySelect.value);
    const date = new Date(year, month - 1, day);
    date.setDate(date.getDate() - 1);
    yearSelect.value = date.getFullYear();
    monthSelect.value = date.getMonth() + 1;
    updateDaySelect(date.getFullYear(), date.getMonth() + 1);
    daySelect.value = date.getDate();
    hourSelect.selectedIndex = 11;
  }
}

// 后一个时辰按钮点击事件处理函数
function nextHour() {
  const hourSelect = document.getElementById('hour');
  let currentIndex = hourSelect.selectedIndex;
  if (currentIndex < 11) {
    hourSelect.selectedIndex = currentIndex + 1;
  } else {
    const year = parseInt(yearSelect.value);
    const month = parseInt(monthSelect.value);
    let day = parseInt(daySelect.value);
    const date = new Date(year, month - 1, day);
    date.setDate(date.getDate() + 1);
    yearSelect.value = date.getFullYear();
    monthSelect.value = date.getMonth() + 1;
    updateDaySelect(date.getFullYear(), date.getMonth() + 1);
    daySelect.value = date.getDate();
    hourSelect.selectedIndex = 0;
  }
}

// 设置当前时间按钮点击事件处理函数
function setCurrentTime() {
  const currentDate = new Date();
  const currentYear = currentDate.getFullYear();
  const currentMonth = currentDate.getMonth() + 1;
  const currentDay = currentDate.getDate();
  const currentHourIndex = Math.floor(currentDate.getHours() / 2);

  yearSelect.value = currentYear;
  monthSelect.value = currentMonth;
  updateDaySelect(currentYear, currentMonth);
  daySelect.value = currentDay;
  const hourSelect = document.getElementById('hour');
  hourSelect.selectedIndex = currentHourIndex;
}