
Create View vUserCurrentOQStatusByUserUID
AS
Select UserUID, Min(ExpireDate) [ExpiringDate], CASE WHEN Min(ExpireDate) < getdate() THEN 'Lapsed' ELSE 'Current' END [Status] from 
(select UserUID, MIN(OQExpireDate) [ExpireDate] from [dbo].[tInspectorOQLog] where OQProfile not in ('OQ-0401', 'OQ-0403') Group By UserUID
Union select UserUID, MAX(OQExpireDate) [ExpireDate] from [dbo].[tInspectorOQLog] where OQProfile in ('OQ-0401', 'OQ-0403') Group By UserUID) s
Group By UserUID