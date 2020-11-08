<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201102201053 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tricks_video (tricks_id INT NOT NULL, video_id INT NOT NULL, INDEX IDX_A5F7E4453B153154 (tricks_id), INDEX IDX_A5F7E44529C1004E (video_id), PRIMARY KEY(tricks_id, video_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tricks_video ADD CONSTRAINT FK_A5F7E4453B153154 FOREIGN KEY (tricks_id) REFERENCES tricks (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tricks_video ADD CONSTRAINT FK_A5F7E44529C1004E FOREIGN KEY (video_id) REFERENCES video (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tricks_video DROP FOREIGN KEY FK_A5F7E44529C1004E');
        $this->addSql('DROP TABLE tricks_video');
        $this->addSql('DROP TABLE video');
    }
}
