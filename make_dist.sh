# apply patches
for P in `ls ../patches/*.patch`
do
	patch -p0 < $P
done

