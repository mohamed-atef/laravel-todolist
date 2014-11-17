<?php

return array(
	'gsd'=>array(
        'folder'             => '/home/mohamed/Documents/gsd',
        'extension'          => '.txt',
        'listOrder'          => array('inbox', 'actions', 'waiting', 'someday', 'calender'),
        'defaultList'        => 'actions',
        'noListPrompt'       => true,
        'dateCompleteFormat' => 'n/j/y',
        'dateDueFormat'      => 'M-j'
        ),

    'aliases'=>array(
        'gsd:list'    => array('gsd:ls'),
        'gsd:listall' => array('gsd:la'),
        'gsd:move'    => array('gsd:mv'),
        'gsd:remove'  => array('gsd:rm'),
        ),
    );
