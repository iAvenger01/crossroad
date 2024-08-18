<?php

namespace App\Foundation\Storage;

use Couchbase\Bucket;
use Couchbase\BucketInterface;
use Couchbase\ClusterOptions;
use Couchbase\Cluster;
use Couchbase\Collection;
use Couchbase\CollectionInterface;
use Couchbase\Scope;
use Couchbase\ScopeInterface;

class CouchbaseStorage implements StorageInterface
{
    private Cluster $cluster;
    private BucketInterface|Bucket $bucket;
    private CollectionInterface|Collection $collection;
    private ScopeInterface|Scope $scope;

    public function getData()
    {
        $exists = $this->collection->exists('data');
        if ($exists->exists()) {
            return $this->collection->get('data')->content();
        }
        return [];
    }

    public function saveData(array $data)
    {
        $this->collection->upsert('data', $data);
    }

    public function configure(): void
    {
        $connectionString = "couchbase://couchbase";
        $options = new ClusterOptions();

        $options->credentials("Administrator", "password");
        $this->cluster = new Cluster($connectionString, $options);
        $this->bucket = $this->cluster->bucket("crossroad");
        $this->collection = $this->bucket->defaultCollection();
    }
}