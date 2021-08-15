<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%lecturer_assessment}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%assessments}}`
 */
class m210815_132203_create_lecturer_assessment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%lecturer_assessment}}', [
            'id' => $this->primaryKey(),
            'lecturer_id' => $this->integer(11)->notNull(),
            'assessment_id' => $this->integer(11)->notNull(),
        ]);

        // creates index for column `lecturer_id`
        $this->createIndex(
            '{{%idx-lecturer_assessment-lecturer_id}}',
            '{{%lecturer_assessment}}',
            'lecturer_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-lecturer_assessment-lecturer_id}}',
            '{{%lecturer_assessment}}',
            'lecturer_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `assessment_id`
        $this->createIndex(
            '{{%idx-lecturer_assessment-assessment_id}}',
            '{{%lecturer_assessment}}',
            'assessment_id'
        );

        // add foreign key for table `{{%assessments}}`
        $this->addForeignKey(
            '{{%fk-lecturer_assessment-assessment_id}}',
            '{{%lecturer_assessment}}',
            'assessment_id',
            '{{%assessments}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-lecturer_assessment-lecturer_id}}',
            '{{%lecturer_assessment}}'
        );

        // drops index for column `lecturer_id`
        $this->dropIndex(
            '{{%idx-lecturer_assessment-lecturer_id}}',
            '{{%lecturer_assessment}}'
        );

        // drops foreign key for table `{{%assessments}}`
        $this->dropForeignKey(
            '{{%fk-lecturer_assessment-assessment_id}}',
            '{{%lecturer_assessment}}'
        );

        // drops index for column `assessment_id`
        $this->dropIndex(
            '{{%idx-lecturer_assessment-assessment_id}}',
            '{{%lecturer_assessment}}'
        );

        $this->dropTable('{{%lecturer_assessment}}');
    }
}
