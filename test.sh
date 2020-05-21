while true; do
    read -p "Do you wish to remove all user data?" yn
    case $yn in
        [Yy]* ) 
		mysql -Droot_cwp -e "select config from icecast" | while IFS= read -r loop
			do
				echo "\rm -f $loop"
    				echo "$loop"
			done 
		echo Yes; 
		echo Yes Again;
		break;;
        [Nn]* ) break;;
        * ) echo "Please answer yes or no.";;
    esac
done
echo Finish
