# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.box = "precise64"

  config.vm.box_url = "http://files.vagrantup.com/precise64.box"

  config.vm.network :forwarded_port, guest: 80, host: 8080

  config.vm.network :forwarded_port, guest: 8181, host: 8181

  config.vm.network :forwarded_port, guest: 81, host: 8081

  config.vm.provision :puppet, :manifests_path => "puppet/manifests", :module_path => "puppet/modules"

  config.vm.synced_folder ".", "/vagrant", id: "vagrant-root", :owner => "www-data", :group => "www-data"

end
