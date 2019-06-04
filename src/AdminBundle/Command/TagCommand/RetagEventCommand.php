<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Command\TagCommand;

use AdminBundle\Command\BaseCommand;
use AdminBundle\Service\TagManager;
use AppBundle\Entity\Event;
use AppBundle\Entity\Tag;
use AppBundle\Entity\UnknownTag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RetagEventCommand extends BaseCommand
{
    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $entityManager;

    /** @var \AdminBundle\Service\TagManager */
    private $tagManager;

    public function __construct(EntityManagerInterface $entityManager, TagManager $tagManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->tagManager = $tagManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:event:retag')
            ->addArgument('unknown-tag', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'The unknown tag name (case-insensitive)')
            ->setDescription('Retag events with the specified unknown tag(s).');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $names = array_map('strtolower', $input->getArgument('unknown-tag'));
        $tags = $this->loadTags($names);
        $tagNames = array_map(function (UnknownTag $tag) {
            return strtolower($tag->getName());
        }, $tags);

        $output->writeln('Loading events');
        $events = $this->getEvents($tagNames);
        $output->writeln(sprintf('#events: %d', \count($events)));
        foreach ($events as $event) {
            $output->writeln(sprintf('%-16s%s', 'Id:', $event->getId()));
            $output->writeln(sprintf('%-16s%s', 'Name:', $event->getName()));

            $oldTags = $event->getTags()->toArray();
            $oldCustomTags = $event->getCustomTags();
            $this->retag($event, $tagNames, $tags);
            $newTags = $event->getTags()->toArray();
            $newCustomTags = $event->getCustomTags();
            $output->writeln(sprintf(
                '%-16s[%s] -> [%s]',
                'Tags:',
                implode(', ', array_map(function (Tag $tag) {
                    return $tag->getName();
                }, $oldTags)),
                implode(', ', array_map(function (Tag $tag) {
                    return $tag->getName();
                }, $newTags))
            ));
            $output->writeln(sprintf(
                '%-16s[%s] -> [%s]',
                'Custom tags:',
                implode(', ', $oldCustomTags),
                implode(', ', $newCustomTags)
            ));
            $output->writeln('');
        }
    }

    /**
     * @param \AppBundle\Entity\Event $event
     * @param string[]                $tagNames
     * @param UnknownTag[]            $tags
     */
    protected function retag(Event $event, array $tagNames, array $tags)
    {
        $customTags = array_map('strtolower', $event->getCustomTags());
        $names = array_intersect($customTags, $tagNames);
        $this->tagManager->loadTagging($event);
        foreach ($tags as $tag) {
            $tagName = strtolower($tag->getName());
            if (in_array($tagName, $names, true)) {
                $this->tagManager->removeTag($tag->getTag(), $event);
                $this->tagManager->addTag($tag->getTag(), $event);

                $customTags = array_filter(
                    $event->getCustomTags(),
                    function (string $tag) use ($tagName) {
                        return 0 !== strcasecmp($tag, $tagName);
                    }
                );
                $event->setCustomTags($customTags);
            }
        }
        $this->tagManager->saveTagging($event);
        $this->tagManager->loadTagging($event);
    }

    /**
     * @param string[] $names
     *
     * @return \AppBundle\Entity\UnknownTag[]
     */
    protected function loadTags(array $names)
    {
        $tags = $this->entityManager->getRepository(UnknownTag::class)
            ->createQueryBuilder('t')
            ->andWhere('t.tag IS NOT NULL')
            ->andWhere('t.name IN (:names)')
            ->setParameter('names', $names)
            ->getQuery()
            ->execute();

        return $tags;
    }

    /**
     * @param string[] $tagNames
     *
     * @return Event[]
     */
    protected function getEvents(array $tagNames)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $qb */
        $qb = $this->entityManager->getRepository(Event::class)
            ->createQueryBuilder('e');
        foreach ($tagNames as $index => $name) {
            $qb->orWhere('e.customTags LIKE :name_'.$index);
            $qb->setParameter(':name_'.$index, '%'.$name.'%');
        }
        $events = $qb->getQuery()->execute();
        $events = array_filter($events, function (Event $event) use ($tagNames) {
            $customTags = array_map('strtolower', $event->getCustomTags());

            return !empty(array_intersect($customTags, $tagNames));
        });

        return $events;
    }
}
