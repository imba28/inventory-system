<?php


namespace App\Command;


use App\Exceptions\NothingFoundException;
use App\Models\User;
use App\QueryBuilder\QueryBuilderException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateCustomerCommand extends Command
{
    private $passwordEncoder;

    public function __construct(string $name = null, UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct($name);
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure()
    {
        $this
            ->setName('app:user:create')
            ->setDescription('Creates a new user');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $username = $io->ask('username', null, function ($username) {
            try {
                User::find($username, 'username');
                throw new \RuntimeException('Username is already taken.');
            } catch (NothingFoundException $e) {
                return $username;
            }
        });
        $email = $io->ask('email', null, function ($value) {
            if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Please provide a valid email address.');
            }
            return $value;
        });
        $password = $io->ask('password');
        $passwordConfirmation = $io->ask('repeat password');

        if ($password !== $passwordConfirmation) {
            $io->error('Passwords did dot match.');
            return 1;
        }

        $io->block(["Username: $username", "Email address: $email"]);

        if (!$io->confirm('Is this correct?')) {
            return 0;
        }

        try {
            $user = User::new();
            $user->setAll([
                'username' => $username,
                'email' => $email,
                'password' => $this->passwordEncoder->encodePassword($user, $password)
            ]);
            $user->save();
        } catch (QueryBuilderException $e) {
            $io->error($e->getMessage());
            return 1;
        }

        return 0;
    }
}
