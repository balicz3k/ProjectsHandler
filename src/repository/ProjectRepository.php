<?php

require_once 'Repository.php';
require_once __DIR__.'/../models/Project.php';

class ProjectRepository extends Repository
{

    public function getProject(int $id): ?Project
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM public.projects WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($project == false) {
            return null;
        }

        return new Project(
            $project['title'],
            $project['description'],
            $project['image']
        );
    }

    public function getProjectsByUserId($userId)
{
    $statement = $this->database->connect()->prepare(
        'SELECT * FROM public.projects WHERE id_assigned_by = :user_id'
    );
    $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $statement->execute();

    $projects = $statement->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($projects as $project) {
        $result[] = new Project(
            $project['id'],
            $project['title'],
            $project['description'],
            $project['image']
        );
    }

    return $result;
}

    public function addProject(Project $project): void
    {
        session_start();

        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            throw new Exception('User is not logged in.');
        }

        $assignedById = $_SESSION['user_id'] ?? null;
        if($assignedById == null) {
            throw new Exception('User id is null.');
        }

        $date = new DateTime();
        $stmt = $this->database->connect()->prepare('
            INSERT INTO projects (title, description, image, created_at, id_assigned_by)
            VALUES (?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $project->getTitle(),
            $project->getDescription(),
            $project->getImage(),
            $date->format('Y-m-d'),
            $assignedById
        ]);
    }

    public function getProjects(): array
    {
        $result = [];

        $stmt = $this->database->connect()->prepare('
            SELECT * FROM projects;
        ');
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($projects as $project) {
            $result[] = new Project(
                $project['id'],
                $project['title'],
                $project['description'],
                $project['image']
            );
        }

        return $result;
    }

    public function updateProjectTitle($projectId, $title)
    {
        $stmt = $this->database->connect()->prepare('UPDATE projects SET title = :title WHERE id = :id');
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function deleteProject($projectId)
    {
        $stmt = $this->database->connect()->prepare('DELETE FROM projects WHERE id = :id');
        $stmt->bindParam(':id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
    }

}