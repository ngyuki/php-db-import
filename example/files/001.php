<?php
return [
    'aaa' => (function(){
        for ($i=0; $i<3; $i++){
            yield ['id' => $i+1, 'name' => 'ore'];
        }
    })()
];
