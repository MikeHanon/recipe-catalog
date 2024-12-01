<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241201111306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ingredients (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, quantity DOUBLE PRECISION NOT NULL, unit VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE recipe (id SERIAL NOT NULL, created_by_id INT NOT NULL, name VARCHAR(255) NOT NULL, instructions TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DA88B137B03A8386 ON recipe (created_by_id)');
        $this->addSql('COMMENT ON COLUMN recipe.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE recipe_ingredient (id SERIAL NOT NULL, recipe_id INT NOT NULL, ingredient_id INT NOT NULL, unit VARCHAR(255) NOT NULL, quantity DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_22D1FE1359D8A214 ON recipe_ingredient (recipe_id)');
        $this->addSql('CREATE INDEX IDX_22D1FE13933FE08C ON recipe_ingredient (ingredient_id)');
        $this->addSql('ALTER TABLE recipe ADD CONSTRAINT FK_DA88B137B03A8386 FOREIGN KEY (created_by_id) REFERENCES "app_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE recipe_ingredient ADD CONSTRAINT FK_22D1FE1359D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE recipe_ingredient ADD CONSTRAINT FK_22D1FE13933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredients (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE recipe DROP CONSTRAINT FK_DA88B137B03A8386');
        $this->addSql('ALTER TABLE recipe_ingredient DROP CONSTRAINT FK_22D1FE1359D8A214');
        $this->addSql('ALTER TABLE recipe_ingredient DROP CONSTRAINT FK_22D1FE13933FE08C');
        $this->addSql('DROP TABLE ingredients');
        $this->addSql('DROP TABLE recipe');
        $this->addSql('DROP TABLE recipe_ingredient');
    }
}
