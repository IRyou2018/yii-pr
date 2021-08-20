<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%individual_assessment}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%users}}`
 * - `{{%assessments}}`
 */
class m210802_120041_create_individual_assessment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%individual_assessment}}', [
            'id' => $this->primaryKey(),
            'student_id' => $this->integer(11)->notNull(),
            'mark' => $this->integer(3)->notNull(),
            'marked' => $this->boolean(),
            'file_path' => $this->string(255),
            'assessment_id' => $this->integer(11)->notNull(),
        ]);

        // creates index for column `student_id`
        $this->createIndex(
            '{{%idx-individual_assessment-student_id}}',
            '{{%individual_assessment}}',
            'student_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-individual_assessment-student_id}}',
            '{{%individual_assessment}}',
            'student_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `assessment_id`
        $this->createIndex(
            '{{%idx-individual_assessment-assessment_id}}',
            '{{%individual_assessment}}',
            'assessment_id'
        );

        // add foreign key for table `{{%assessments}}`
        $this->addForeignKey(
            '{{%fk-individual_assessment-assessment_id}}',
            '{{%individual_assessment}}',
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
        // drops foreign key for table `{{%users}}`
        $this->dropForeignKey(
            '{{%fk-individual_assessment-student_id}}',
            '{{%individual_assessment}}'
        );

        // drops index for column `student_id`
        $this->dropIndex(
            '{{%idx-individual_assessment-student_id}}',
            '{{%individual_assessment}}'
        );

        // drops foreign key for table `{{%assessments}}`
        $this->dropForeignKey(
            '{{%fk-individual_assessment-assessment_id}}',
            '{{%individual_assessment}}'
        );

        // drops index for column `assessment_id`
        $this->dropIndex(
            '{{%idx-individual_assessment-assessment_id}}',
            '{{%individual_assessment}}'
        );

        $this->dropTable('{{%individual_assessment}}');
    }
}
