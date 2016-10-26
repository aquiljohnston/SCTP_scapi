CREATE TABLE [dbo].[tTabletDataInsertBreadcrumbArchive] (
    [tTabletDataInsertArchiveBreadcrumbID] INT                IDENTITY (1, 1) NOT NULL,
    [ClientID]                             INT                NULL,
    [UserUID]                              VARCHAR (100)      NULL,
    [SvrDTLT]                              DATETIME           CONSTRAINT [DFtTabletDataInsertArchiveBC_SvrDTLT] DEFAULT (getdate()) NULL,
    [SvrDTLT_Offset]                       DATETIMEOFFSET (7) CONSTRAINT [DFtTabletDataInsertArchiveBC_SvrDTLT_Offset] DEFAULT (sysdatetimeoffset()) NULL,
    [TransactionType]                      VARCHAR (50)       NULL,
    [InsertedData]                         VARCHAR (MAX)      NULL
);

