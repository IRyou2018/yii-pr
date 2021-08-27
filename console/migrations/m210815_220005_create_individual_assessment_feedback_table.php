<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%individual_assessment_feedback}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%items}}`
 * - `{{%individual_assessment}}`
 */
class m210815_220005_create_individual_assessment_feedback_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%individual_assessment_feedback}}', [
            'id' => $this->primaryKey(),
            'student_id' => $this->integer(11)->notNull(),
            'mark' => $this->integer(3),
            'comment' => 'LONGTEXT',
            'item_id' => $this->integer(11)->notNull(),
            'individual_assessment_id' => $this->integer(11)->notNull(),
        ]);

        // creates index for column `student_id`
        $this->createIndex(
            '{{%idx-individual_assessment_feedback-student_id}}',
            '{{%individual_assessment_feedback}}',
            'student_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-individual_assessment_feedback-student_id}}',
            '{{%individual_assessment_feedback}}',
            'student_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `item_id`
        $this->createIndex(
            '{{%idx-individual_assessment_feedback-item_id}}',
            '{{%individual_assessment_feedback}}',
            'item_id'
        );

        // add foreign key for table `{{%items}}`
        $this->addForeignKey(
            '{{%fk-individual_assessment_feedback-item_id}}',
            '{{%individual_assessment_feedback}}',
            'item_id',
            '{{%items}}',
            'id',
            'CASCADE'
        );

        // creates index for column `individual_assessment_id`
        $this->createIndex(
            '{{%idx-individual_assessment_feedback-individual_assessment_id}}',
            '{{%individual_assessment_feedback}}',
            'individual_assessment_id'
        );

        // add foreign key for table `{{%individual_assessment}}`
        $this->addForeignKey(
            '{{%fk-individual_assessment_feedback-individual_assessment_id}}',
            '{{%individual_assessment_feedback}}',
            'individual_assessment_id',
            '{{%individual_assessment}}',
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
            '{{%fk-individual_assessment_feedback-student_id}}',
            '{{%individual_assessment_feedback}}'
        );

        // drops index for column `student_id`
        $this->dropIndex(
            '{{%idx-individual_assessment_feedback-student_id}}',
            '{{%individual_assessment_feedback}}'
        );

        // drops foreign key for table `{{%items}}`
        $this->dropForeignKey(
            '{{%fk-individual_assessment_feedback-item_id}}',
            '{{%individual_assessment_feedback}}'
        );

        // drops index for column `item_id`
        $this->dropIndex(
            '{{%idx-individual_assessment_feedback-item_id}}',
            '{{%individual_assessment_feedback}}'
        );

        // drops foreign key for table `{{%individual_assessment}}`
        $this->dropForeignKey(
            '{{%fk-individual_assessment_feedback-individual_assessment_id}}',
            '{{%individual_assessment_feedback}}'
        );

        // drops index for column `individual_assessment_id`
        $this->dropIndex(
            '{{%idx-individual_assessment_feedback-individual_assessment_id}}',
            '{{%individual_assessment_feedback}}'
        );

        $this->dropTable('{{%individual_assessment_feedback}}');
    }
}
