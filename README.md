# Delete Hidden Posts

## Description

This is a plugin for **Question2Answer** websites that deletes all hidden posts with/with-out having children posts  

The latest version of Question2Answer does not allow to delete the posts directly which have some children posts (the comments and answers for a question are said to be children posts for that question and the comments to a answer are said to be children posts to a answer ) . You must have to hide and then delete all its child posts one by one before you delete the parent one . This applies to both the Quesions and answers as well . 

This plugin adds a delete button right after the question / answer / comment (can be controlled via admin panel options ) which can delete the posts (question / amswer / comment) on a single click . 

Also if your website has many hidden posts which have many child posts this plugin can delete them with one click effort .

## Features

- ability to delete all the hidden posts from q2a database having dependencies 
- when a question is deleted all its answers , comments gets deleted 
- when a answer is deleted all its comments and related questions gets deleted 
- buttons are displayed *after* a question's or answer's or comemnt's response buttons - which helps to delete the post right away 
- every options can be configured from admin panel 

## Version History

- 1.1
    * added ask user confirmation while deleting a question / answer / comment
    * added ask admin confirmation while deleting all hidden posts
    * removed css styling , only handling with q2a provided classes
    * improved redirect logic 
    * improved code 
- 1.0
    * initial stable release 

## Disclaimer

This is **beta** code.  It is probably okay for production environments, but may not work exactly as expected.  Refunds will not be given.  If it breaks, you get to keep both parts.

## About Question2Answer

Question2Answer is a free and open source platform for Q&A sites. For more information, visit:

http://www.question2answer.org/
