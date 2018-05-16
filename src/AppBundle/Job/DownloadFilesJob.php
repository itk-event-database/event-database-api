<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Job;

use ResqueBundle\Resque\ContainerAwareJob;

class DownloadFilesJob extends ContainerAwareJob
{
    public function run($args)
    {
        $className = $args['className'];
        $id = $args['id'];
        $fields = $args['fields'];

        $service = $this->getContainer()->get('download_files');
        $service->process($className, $id, $fields);
    }
}
