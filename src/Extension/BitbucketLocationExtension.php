<?php
/**
 * @copyright Copyright (c) 2017 Julius Härtl <jus@bitgrid.net>
 *
 * @author Julius Härtl <jus@bitgrid.net>
 *
 * @license GNU AGPL version 3 or any later version
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace JuliusHaertl\PHPDocToRst\Extension;

use JuliusHaertl\PHPDocToRst\Builder\FileBuilder;
use JuliusHaertl\PHPDocToRst\Builder\PhpDomainBuilder;
use phpDocumentor\Reflection\Element;
use phpDocumentor\Reflection\Php\File;

/**
 * This extension adds a link to the source at bitbucket to all elements
 *
 * Arguments
 * 0 => Url to the bitbucket repo (required)
 * 1 => Path to the git repository (required)
 * 2 => Branch to link to (default=master)
 */

class BitbucketLocationExtension extends Extension {

    protected $basePath;
    protected $bitbucketRepo;
    protected $branch = 'master';

    public function prepare() {
        if (count($this->arguments) < 2) {
            throw new \Exception('BitbucketLocationExtension requires the following arguments bitbucketUrl, basePath.');
        }
        $this->basePath = $this->arguments[0];
        $this->bitbucketRepo = $this->arguments[1];
        if (count($this->arguments) > 2) {
            $this->branch = $this->arguments[2];
        }
    }

    /**
     * @param string $type
     * @param FileBuilder $builder
     * @param Element $element
     */
    public function render($type, &$builder, $element) {
        if (!$builder instanceof FileBuilder) {
            return;
        }
        if ($type === PhpDomainBuilder::SECTION_AFTER_DESCRIPTION) {
            if (!$builder->getFile() instanceof File) {
                return;
            }
            $filePath = $builder->getFile()->getPath();
            $filePath = preg_replace('/^' . preg_quote($this->basePath, '/') . '/', '', $filePath);
            $lineNumber = $element->getLocation()->getLineNumber();
            $url = $this->getBitbucketLink($filePath, $lineNumber, $this->branch);
            $builder->addFieldList('Source', '`' . $filePath. '#' . $lineNumber . ' <'.$url.'>`_');
        }
    }

    private function getBitbucketLink($file, $line=1, $branch='master') {
        return rtrim($this->bitbucketRepo,'/') . '/'.$file.'?at=refs%2Fheads%2F'.$branch.'#' . $line;
    }

}