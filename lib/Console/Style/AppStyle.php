<?php

namespace NielsHoppe\RDFDAV\Console\Style;

use Symfony\Component\Console\Style\SymfonyStyle;

class AppStyle extends SymfonyStyle {

    public function codeblock ($code, $file = null) {

        if (is_string($file)) {

            $this->newLine();
            $this->writeln($file);
        }
        
        $this->block($code, null, null, '');
    }
}
