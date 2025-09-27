<?php
$form = $props['form'];
$members = $props['members'];
$roles = $props['roles'];

echo $this->load->view('setup/modals/logic', [], true);
echo $this->load->view('setup/modals/add-block', ['form' => $form], true);
echo $this->load->view('setup/modals/design-configuration', ['form' => $form, 'members' => $members, 'roles' => $roles,'props' => $props], true);
echo $this->load->view('setup/modals/publish-form', ['form' => $form], true);
echo $this->load->view('setup/modals/share', ['form' => $form], true);



