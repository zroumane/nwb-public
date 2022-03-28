<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210807184236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, item_key VARCHAR(255) NOT NULL, craft JSON NOT NULL, INDEX IDX_1F1B251E12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item_item_tag (item_id INT NOT NULL, item_tag_id INT NOT NULL, INDEX IDX_8891047A126F525E (item_id), INDEX IDX_8891047A3C2B16DE (item_tag_id), PRIMARY KEY(item_id, item_tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item_category (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, category VARCHAR(255) NOT NULL, INDEX IDX_6A41D10A727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item_tag (id INT AUTO_INCREMENT NOT NULL, tag VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E12469DE2 FOREIGN KEY (category_id) REFERENCES item_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_item_tag ADD CONSTRAINT FK_8891047A126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_item_tag ADD CONSTRAINT FK_8891047A3C2B16DE FOREIGN KEY (item_tag_id) REFERENCES item_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_category ADD CONSTRAINT FK_6A41D10A727ACA70 FOREIGN KEY (parent_id) REFERENCES item_category (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_item_tag DROP FOREIGN KEY FK_8891047A126F525E');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E12469DE2');
        $this->addSql('ALTER TABLE item_category DROP FOREIGN KEY FK_6A41D10A727ACA70');
        $this->addSql('ALTER TABLE item_item_tag DROP FOREIGN KEY FK_8891047A3C2B16DE');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE item_item_tag');
        $this->addSql('DROP TABLE item_category');
        $this->addSql('DROP TABLE item_tag');
    }
}
