<?php
//Controller Temporári

echo User::getCount(['raw' => 'id % 2 = 0']);