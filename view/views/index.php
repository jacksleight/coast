<?php $this->layout('/layout', [
    'title' => 'Lorem Ipsum Dolor',
]) ?>
<?php $this->block('top') ?>

<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quasi, iure, voluptas, iste, nulla expedita similique velit earum illum nisi quisquam excepturi cupiditate cum voluptatum odit hic repellendus laboriosam fugiat aliquid.</p>
<?php echo $this->render('partial') ?>
<?php echo $this->render('partial', [], 'other') ?>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Culpa, quo, deleniti perferendis eaque atque perspiciatis quos iste illum a at quae ducimus architecto consequatur eveniet vero hic quas explicabo labore.</p>

<?php $this->block('bottom') ?>

<ul>
    <li>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Deleniti, sequi.</li>
    <li>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Deleniti, sequi.</li>
    <li>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Deleniti, sequi.</li>
    <li>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Deleniti, sequi.</li>
    <li>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Deleniti, sequi.</li>
</ul>