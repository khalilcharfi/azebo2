<?php
/**
 * azebo2 is an application to print working time tables
 * Copyright (C) 2019  Emanuel Minetti
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author Emanuel Minetti <e.minetti@posteo.de>
 * @link      https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright (c) 2019 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

/**
 * List of enabled modules for this application.
 *
 * This should be an array of module namespaces used in the application.
 */
return [
    'Zend\Db',
    'Zend\Router',
    'Zend\Validator',
    'Application',
    'Login',
    'WorkingTime',
];
