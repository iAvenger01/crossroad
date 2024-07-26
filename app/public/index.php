<?php
use App\App;

require_once __DIR__ . "/../vendor/autoload.php";

$app = new App();
$settings = $app->getSettings();

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Перекресток</title>
        <style>
            .container {
                width: 100%;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div>
                <h1>
                    Настройки
                </h1>
                <form method="post" action="setSettings.php">
                    <label>
                        Нужен ли светофор
                        <select name="traffic_light_need">
                            <option <?php echo $settings['traffic_light_need'] ? 'selected' : '' ?> value="true">Да</option>
                            <option <?php echo $settings['traffic_light_need'] ? '' : 'selected' ?> value="false">Нет</option>
                        </select>
                    </label>
                    <br>
                    <label>
                        Время переключения светофора
                        <input type="number" name="traffic_light_duration" value="<?php echo $settings['traffic_light_duration'] ?>" min="0" max="1000">
                    </label>
                    <br>
                    <label>
                        Максимальная скорость автомобиля
                        <input type="number" name="car_max_speed" value="<?php echo $settings['car_max_speed']?>" min="0" max="200">
                    </label>
                    <br>
                    <label>
                        Максимальная длина дороги
                        <input type="number" name="road_length" value="<?php echo $settings['road_length'] ?>" min="0" max="1000">
                    </label>
                    <br>
                    <label>
                        Максимальная скорость дороги
                        <input type="number" name="max_lane_speed" value="<?php echo $settings['max_lane_speed'] ?>" min="0" max="200">
                    </label>
                    <br>
                    <label>
                        Интенсивность трафика
                        <input type="number" name="traffic_intensity" value="<?php echo $settings['traffic_intensity'] ?>" min="0" max="1000">
                    </label>
                    <br>
                    <label>
                        Нужен ли светофор
                        <select name="priority">
                            <option <?php echo $settings['priority'] === 'horizontal' ? 'selected' : '' ?> value="horizontal">Горизонтальные дороги</option>
                            <option <?php echo $settings['priority'] === 'vertical' ? 'selected' : '' ?> value="vertical">Вертикальные дороги</option>
                        </select>
                    </label>
                    <br>
                    Ограничения поворотов:
                    <?php
                        foreach ($settings['constraint_turns'] as $key => $constraint_turn) {
                    ?>
                        <label>
                            <?php echo $key ?>
                            <select name="constraint_turns[<?php echo $key ?>][]" multiple>
                                <?php
                                    foreach (['left', 'right', 'top', 'bottom'] as $item) {
                                ?>
                                        <option <?php echo in_array($item, $settings['constraint_turns'][$key]) ? 'selected' : '' ?> value="<?php echo $item ?>"><?php echo $item ?></option>
                                <?php
                                    }
                                ?>
                            </select>
                        </label>
                    <?php
                        }
                    ?>
                    <p><input type="submit" value="Отправить"></p>
                </form>
            </div>
            <div>
                <canvas height="1036" width="1036" id="canvas"></canvas>
            </div>
        </div>
        <script>
            let roadLength = 0;
            let carLength = 14;
            let offsetStroke = 1;
            let roadHeight = 36;
            let crossRoadLength = roadHeight;
            let laneHeight = roadHeight / 2 - 4;
            let canvas = document.getElementById('canvas');
            let ctx = canvas.getContext('2d');
            ctx.strokeRect(0, 0, canvas.width, canvas.height);
            // let timer = setInterval(getData, 1000);
            getData();

            function getData() {
                let xhr = new XMLHttpRequest();
                xhr.open('GET', 'getCrossRoad.php');
                xhr.send();
                xhr.onload = () => {
                    if (xhr.status === 200) {
                        render(JSON.parse(xhr.responseText));
                    }
                }
            }

            function render(data) {
                let width = 14 * 2 + data.road_length * 2 + 2 + roadHeight
                let height = width;
                canvas.width = width;
                canvas.height = height;
                ctx.strokeStyle = 'blue';
                ctx.strokeRect(0, 0, width, height);
                ctx.strokeStyle = '#000';
                roadLength = data.road_length;
                let crossRoad = data.cross_road;
                let trafficLight = crossRoad.traffic_light;
                let offsetForCrossRoad = offsetStroke + carLength + roadLength;
                renderFigure(offsetForCrossRoad, offsetForCrossRoad, crossRoadLength, crossRoadLength, '#778899');
                for (let [direction, car] of Object.entries(data.cross_road.cars_buffer)) {
                    renderCarBuffer(direction, car);
                }
                for (let [, road] of Object.entries(data.cross_road.roads)) {
                    renderRoad(road.direction, data.road_length);
                    renderLane(road.direction, data.road_length);
                    renderLane(road.direction, data.road_length, true);
                    if (data.cross_road.traffic_light !== null) {
                        renderTrafficLight(
                            road.direction, data.road_length,
                            trafficLight.signals.vertical.color, trafficLight.signals.horizontal.color
                        );
                    }
                    for (let [, car] of Object.entries(road.straight_lane.cars)) {
                        renderCar(road.direction, car.position_on_lane, false, car.color);
                    }
                    for (let [, car] of Object.entries(road.reverse_lane.cars)) {
                        renderCar(road.direction, car.position_on_lane, true, car.color);
                    }
                }
            }

            function renderCarBuffer(direction, car) {
                let x, y;
                switch (direction) {
                    case 'left':
                        x = offsetStroke + carLength + roadLength + (crossRoadLength - carLength * 2) / 2 / 2;
                        y = offsetStroke + carLength + roadLength + (crossRoadLength - carLength - 2);
                        break;
                    case 'right':
                        x = offsetStroke + carLength + roadLength + (crossRoadLength - carLength - 2);
                        y = offsetStroke + carLength + roadLength + (crossRoadLength - carLength * 2) / 2 / 2;
                        break;
                    case 'top':
                        x = offsetStroke + carLength + roadLength + (crossRoadLength - carLength * 2) / 2 / 2;
                        y = offsetStroke + carLength + roadLength + (crossRoadLength - carLength * 2) / 2 / 2;
                        break;
                    case 'bottom':
                        x = offsetStroke + carLength + roadLength + (crossRoadLength - carLength - 2);
                        y = offsetStroke + carLength + roadLength + (crossRoadLength - carLength - 2);
                        break;
                }
                ctx.fillStyle = car !== null && Object.hasOwn(car, 'color') ? car.color : 'white';
                ctx.fillRect(x, y, carLength, carLength);
            }

            function renderTrafficLight(direction, length, verticalColor = 'red', horizontalColor = 'green') {
                if (direction === 'left') {
                    let coordinate = getCoordinateOffset(direction, roadLength, roadLength - 25, 43);
                    ctx.strokeRect(coordinate.x, coordinate.y, 32, 20, 'black');
                    if (horizontalColor === 'red') {
                        renderFigure(coordinate.x + 16, coordinate.y + 3, 14, 14, horizontalColor);
                    } else {
                        renderFigure(coordinate.x + 2, coordinate.y + 3, 14, 14, horizontalColor);
                    }
                } else if (direction === 'right') {
                    let coordinate = getCoordinateOffset(direction, roadLength, 8, - 26);
                    ctx.strokeRect(coordinate.x, coordinate.y, 32, 20, 'black');
                    if (horizontalColor === 'red') {
                        console.log(horizontalColor, verticalColor)
                        renderFigure(coordinate.x + 2, coordinate.y + 3, 14, 14, horizontalColor);
                    } else {
                        renderFigure(coordinate.x + 16, coordinate.y + 3, 14, 14, horizontalColor);
                    }
                } else if (direction === 'top') {
                    let coordinate = getCoordinateOffset(direction, roadLength, -26, roadLength - 25);
                    ctx.strokeRect(coordinate.x, coordinate.y, 20, 32, 'black');
                    if (verticalColor ==='red') {
                        renderFigure(coordinate.x + 3, coordinate.y + 16, 14, 14, verticalColor);
                    } else {
                        renderFigure(coordinate.x + 3, coordinate.y + 2, 14, 14, 'green');
                    }
                } else if (direction === 'bottom') {
                    let coordinate = getCoordinateOffset(direction, roadLength, 43, 8);
                    ctx.strokeRect(coordinate.x, coordinate.y, 20, 32, 'black');
                    if (verticalColor === 'red') {
                        renderFigure(coordinate.x + 3, coordinate.y + 2, 14, 14, verticalColor);
                    } else {
                        renderFigure(coordinate.x + 3, coordinate.y + 16, 14, 14, verticalColor);
                    }
                }
            }

            function renderRoad(direction, length) {
                let coordinate = getCoordinateOffset(direction, length);
                if (getOrientation(direction) === 'horizontal') {
                    renderFigure(coordinate.x, coordinate.y, length + carLength, roadHeight);
                } else {
                    renderFigure(coordinate.x, coordinate.y, roadHeight, length + carLength);
                }
            }

            function renderLane(direction, length, reverse = false) {
                let color = reverse ? '#A9A9A9' : 'grey';
                let offset;
                if (direction === 'right' || direction === 'top') {
                    offset = (roadHeight - laneHeight * 2) / 2 / 2;
                    if (reverse === false) {
                        offset = offset * 3 + laneHeight;
                    }
                } else {
                    offset = (roadHeight - laneHeight * 2) / 2 / 2;
                    if (reverse) {
                        offset = offset * 3 + laneHeight;
                    }
                }
                if (getOrientation(direction) === 'horizontal') {
                    let coordinate = getCoordinateOffset(direction, length, 0, offset);
                    renderFigure(coordinate.x, coordinate.y, length + carLength, laneHeight, color);
                } else {
                    let coordinate = getCoordinateOffset(direction, length, offset, 0);
                    renderFigure(coordinate.x, coordinate.y, laneHeight, length + carLength, color);
                }
            }

            function renderCar(direction, positionOnLane, reverse, color = 'black') {
                switch (direction) {
                    case 'right':
                        positionOnLane = reverse ? positionOnLane : -positionOnLane + roadLength;
                        break;
                    case 'bottom':
                        positionOnLane = reverse ? positionOnLane : -positionOnLane + roadLength;
                        break;
                    case 'top':
                        positionOnLane = reverse ? -positionOnLane + roadLength : positionOnLane;
                        break;
                    case 'left':
                        positionOnLane = reverse ? -positionOnLane + roadLength : positionOnLane;
                        break;
                }
                let offset;
                if (direction === 'left' || direction === 'bottom') {
                    offset = (roadHeight - laneHeight * 2) / 2 / 2;
                    if (reverse === false) {
                        offset = offset * 3 + laneHeight;
                    }
                } else {
                    offset = (roadHeight - laneHeight * 2) / 2 / 2;
                    if (reverse) {
                        offset = offset + 4 + laneHeight;
                    }
                }
                if (getOrientation(direction) === 'horizontal') {
                    let coordinate = getCoordinateOffset(direction, roadLength, positionOnLane, offset);
                    renderFigure(coordinate.x, coordinate.y, carLength, carLength, color);
                } else {
                    let coordinate = getCoordinateOffset(direction, roadLength, offset, positionOnLane);
                    renderFigure(coordinate.x, coordinate.y, carLength, carLength, color);
                }
            }

            function getCoordinateOffset(direction, length, offsetX = 0, offsetY = 0) {
                let x, y;
                switch (direction) {
                    case 'left':
                        x = offsetStroke + offsetX;
                        y = offsetStroke + carLength + length + offsetY;
                        break;
                    case 'right':
                        x = offsetStroke + carLength + crossRoadLength + length + offsetX;
                        y = offsetStroke + carLength + length + offsetY;
                        break;
                    case 'top':
                        x = offsetStroke + carLength + length + offsetX;
                        y = offsetStroke + offsetY;
                        break;
                    case 'bottom':
                        x = offsetStroke + carLength + length + offsetX;
                        y = offsetStroke + carLength + crossRoadLength + length + offsetY;
                        break;
                }
                return {x: x, y: y}
            }

            function getOrientation(direction) {
                if (direction === 'left' || direction === 'right') {
                    return 'horizontal'
                }
                return 'vertical';
            }

            function renderFigure(offsetX, offsetY, length, width, color = 'black') {
                ctx.fillStyle = color
                ctx.fillRect(offsetX, offsetY, length, width)
            }
        </script>
    </body>
</html>