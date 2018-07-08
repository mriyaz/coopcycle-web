<?php

namespace AppBundle\Command;

// use AppBundle\Entity\RemotePushToken;
// use Ehann\RediSearch\Index;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchIndexCommand extends ContainerAwareCommand
{
    // private $notificationManager;
    // private $userManager;
    // private $remotePushTokenRepository;

    private $search;

    protected function configure()
    {
        $this
            ->setName('coopcycle:search:index')
            ->setDescription('Creates search indexes.')
            // ->addArgument('username', InputArgument::REQUIRED, 'The username.')
            // ->addArgument('message', InputArgument::REQUIRED, 'The message.');;
            ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->search = $this->getContainer()->get('coopcycle.search');
        // $this->userManager = $this->getContainer()->get('fos_user.user_manager');
        // $this->remotePushTokenRepository = $this->getContainer()->get('doctrine')->getRepository(RemotePushToken::class);
        // $this->remotePushNotificationManager = $this->getContainer()->get('coopcycle.remote_push_notification_manager');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->search->getAddressIndex();
        // $addressIndex = new Index()
        // $username = $input->getArgument('username');
        // $message = $input->getArgument('message');

        // $user = $this->userManager->findUserByUsername($username);

        // $tokens = $this->remotePushTokenRepository->findByUser($user);
        // if (count($tokens) === 0) {
        //     $output->writeln(sprintf('<error>User %s has no remote push tokens configured</error>', $username));
        //     return;
        // }

        // foreach ($tokens as $token) {
        //     $output->writeln(sprintf('<info>Sending remote push notification to platform %s</info>', $token->getPlatform()));
        //     $this->remotePushNotificationManager->send($message, $token);
        // }
    }
}
