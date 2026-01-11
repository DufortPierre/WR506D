<?php

namespace App\Command;

use App\Repository\ActorRepository;
use App\Repository\CategoryRepository;
use App\Repository\MediaObjectRepository;
use App\Repository\MovieRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:stats',
    description: 'Affiche les statistiques de l\'application (films, acteurs, catégories, images)',
)]
class AppStatsCommand extends Command
{
    public function __construct(
        private MovieRepository $movieRepository,
        private ActorRepository $actorRepository,
        private CategoryRepository $categoryRepository,
        private MediaObjectRepository $mediaObjectRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, 'Type de données demandées (movies, actors, categories, images, all)')
            ->addOption('log-file', null, InputOption::VALUE_OPTIONAL, 'Chemin vers le fichier de log')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getArgument('type');
        $logFile = $input->getOption('log-file');

        $output->writeln("Affichage de la valeur type : {$type}");
        
        switch ($type) {
            case 'movies':
                $this->displayMoviesStats($io, $output);
                break;
            case 'actors':
                $this->displayActorsStats($io, $output);
                break;
            case 'categories':
                $this->displayCategoriesStats($io, $output);
                break;
            case 'images':
                $this->displayImagesStats($io, $output);
                break;
            case 'all':
                $this->displayMoviesStats($io, $output);
                $this->displayActorsStats($io, $output);
                $this->displayCategoriesStats($io, $output);
                $this->displayImagesStats($io, $output);
                break;
            default:
                $io->error("Type invalide. Types acceptés : movies, actors, categories, images, all");
                return Command::FAILURE;
        }

        // Si un fichier de log est spécifié, écrire les résultats
        if ($logFile) {
            // Ici on pourrait écrire dans le fichier de log
            $io->note("Log file option: {$logFile}");
        }

        $io->success('Statistiques affichées avec succès!');

        return Command::SUCCESS;
    }

    private function displayMoviesStats(OutputInterface $output): void
    {
        $count = $this->movieRepository->count([]);
        $output->writeln("Nombre de films : {$count}");
    }

    private function displayActorsStats(OutputInterface $output): void
    {
        $count = $this->actorRepository->count([]);
        $output->writeln("Nombre d'acteurs : {$count}");
    }

    private function displayCategoriesStats(OutputInterface $output): void
    {
        $categories = $this->categoryRepository->findAll();
        $count = count($categories);
        $output->writeln("Nombre de catégories : {$count}");
        
        foreach ($categories as $category) {
            $moviesCount = $category->getMovies()->count();
            $output->writeln("  - {$category->getName()}: {$moviesCount} film(s)");
        }
    }

    private function displayImagesStats(OutputInterface $output): void
    {
        $mediaObjects = $this->mediaObjectRepository->findAll();
        $count = count($mediaObjects);
        
        // Calcul de la taille (approximative, car on stocke des URLs, pas des fichiers)
        // Pour l'exercice, on va simuler une taille moyenne par image
        $averageSizePerImage = 2; // Mo (approximatif)
        $totalSize = $count * $averageSizePerImage;
        
        $output->writeln("Nombre d'images : {$count}");
        $output->writeln("Taille de stockage estimée : {$totalSize} Mo");
    }
}
