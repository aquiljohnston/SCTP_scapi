
/****** Script for SelectTopNRows command from SSMS  ******/
CREATE View [dbo].[vTabletFilter]
AS

SELECT Distinct FilterSizeType, FilterMfgType, FilterModelType
FROM [dbo].[rFilter]
Where ActiveFlag = 1 and StatusType = 'Active'
