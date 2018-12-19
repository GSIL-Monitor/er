#!/bin/bash
for file in `ls ./sp`
do
   cat "./sp/"$file >> ./sp.sql
   cat "./sp/empty.sql" >> ./sp.sql
done
