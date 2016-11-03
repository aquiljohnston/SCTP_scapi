CREATE VIEW [dbo].[v_ActivityTb_DEV] AS 
SELECT 
      [ActivityUID]
     ,[ActivityCreatedUserUID]
     ,[ActivityType]
     ,[ActivityBatteryLevel]
     ,[ActivityStartTime]
     ,[ActivityEndTime]
     ,[ActivityTitle]
     ,[ActivityCreateDate]
     ,[ActivityLatitude]
     ,[ActivityLongitude]
FROM [ActivityTb]