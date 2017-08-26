<?php
return [
    'bbb' => (function(){
        for ($i=0; $i<3; $i++){
            yield ['id' => null, 'name' => 'are'];
        }
    })(),
    'aaa' => (function(){
        for ($i=0; $i<3; $i++){
            yield ['id' => null, 'name' => 'are'];
        }
    })(),
];
