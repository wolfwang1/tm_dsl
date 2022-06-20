FROM registry.cn-beijing.aliyuncs.com/g7/truckmanager-php-dsl:latest
ADD ./ /data/project
WORKDIR /data/project
CMD ["php","start.php","start"]
#docker build -t truck_manager_dsl:latest .